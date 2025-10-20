<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Models\CourierData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;

class CourierController extends Controller
{
    public function checkCourierData(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'phone' => 'required|regex:/^\d{11}$/',
            'refer' => 'nullable|string'
        ]);

        $user = User::where('api_key', $request->api_key)->first();
        if (!$user || $user->status !== 'active') {
            return response()->json(['error' => 'Unauthorized or inactive user'], 403);
        }

        // TODO
        // if ($user->api_call_limit <= 0) {
        //     return response()->json(['error' => 'API call limit exceeded'], 403);
        // }
        $phone = $request->input('phone');
        // First check if exists in DB within 7 days
        // 🔎 Check if cached data exists within last 7 days
        $existing = CourierData::where('phone', $phone)
            ->where('called_at', '>=', now()->subDays(7))
            ->first();

        if ($existing) {
            return response()->json([
                'phone' => $phone,
                'courierData' => json_decode($existing->courier_response, true),
                'source' => 'cached',
                'called_at' => $existing->called_at
            ]);
        }

        // ❌ No fresh cache → call courier APIs
        $results = $this->getCourierData($phone);
        // Save or update globally unique phone record
        DB::table('courier_data')->updateOrInsert(
            ['phone' => $phone], // ✅ uniqueness condition is only phone
            [
                'user_id' => $user->id, // update latest user who checked
                'called_at' => now(),
                'courier_response' => json_encode($results),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'refer' => $request->refer,
                'type' => 'customer check',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // TODO
        // $cacheKey = 'courier_data_' . $phone;
        // $results = Cache::remember($cacheKey, 300, function () use ($phone) {
        //     return $this->getCourierData($phone);
        // });

        // $user->decrement('api_call_limit');

        return response()->json([
            'phone' => $phone,
            'courierData' => $results,
            'source' => 'fresh',
            'called_at' => now()
        ]);
    }

    private function getCourierData($phone)
    {
        $settings = Setting::whereIn('key', [
            'steadfast_cookie',
            'redex_api_token',
            'paperfly_api_token',
            'pathao_api_token',
        ])->pluck('value', 'key');

        $pathao_token = $settings['pathao_api_token'] ?? null;
        $steadfast_cookie = $settings['steadfast_cookie'] ?? null;
        $redxToken = $settings['redex_api_token'] ?? null;
        $paperfly_token = $settings['paperfly_api_token'] ?? null;

        $responses = Http::pool(fn($pool) => [
            'pathao' => $pool->as('pathao')->withHeaders([
                'authorization' => "Bearer {$pathao_token}",
                'content-type' => 'application/json',
            ])->post('https://merchant.pathao.com/api/v1/user/success', ['phone' => $phone]),

            'steadfast' => $pool->as('steadfast')->withHeaders([
                "cookie" => $steadfast_cookie,
                "User-Agent" => 'Mozilla/5.0',
                "Accept" => 'application/json, text/plain, */*'
            ])->get("https://steadfast.com.bd/user/frauds/check/{$phone}"),

            'redx' => $pool->as('redx')->withHeaders([
                'Cookie' => '$redxToken',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept' => 'application/json, text/plain, */*',
                'Connection' => 'keep-alive',
                'Referer' => 'https://redx.com.bd/',
            ])->get("https://redx.com.bd/api/redx_se/admin/parcel/customer-success-return-rate?phoneNumber={$phone}"),

            'paperfly' => $pool->as('paperfly')->withHeaders([
                'authorization' => "Bearer {$paperfly_token}",
                'content-type' => 'application/json'
            ])->post('https://go-app.paperfly.com.bd/merchant/api/react/smart-check/list.php', ['search_text' => $phone]),
        ]);

        $pathao = $responses['pathao']->json();
        $steadfast = $responses['steadfast']->json();
        if (!$steadfast) {
            // Step 1: Login & get new cookie
            $cookieJar = new FileCookieJar(storage_path('session_cookies.txt'), true);
            // $client = new Client([
            //     'base_uri' => 'https://steadfast.com.bd',
            //     'cookies' => $cookieJar,
            //     'headers' => [
            //         'User-Agent' => 'Mozilla/5.0',
            //     ],
            // ]);
            $client = new Client([
                'base_uri' => 'https://steadfast.com.bd',
                'cookies' => $cookieJar,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
            ]);

            try {
                $loginPage = $client->get('/login')->getBody()->getContents();
                $crawler = new Crawler($loginPage);
                $csrf = $crawler->filter('input[name="_token"]')->attr('value');

                $client->post('/login', [
                    'form_params' => [
                        '_token' => $csrf,
                        'email' => 'ak.codemoly@gmail.com',
                        'password' => 'CodeMoly@2025',
                    ],
                ]);

                // Get session cookie
                $cookieJar = new FileCookieJar(storage_path('session_cookies.txt'), true);
                $sessionCookie = null;
                foreach ($cookieJar->toArray() as $cookie) {
                    if ($cookie['Name'] === 'steadfast_courier_session') {
                        $sessionCookie = $cookie['Value'];
                        break;
                    }
                }

                // Update to DB
                if ($sessionCookie) {
                    Setting::where('key', 'steadfast_cookie')->update([
                        'value' => "steadfast_courier_session={$sessionCookie}",
                        'updated_at' => Carbon::now(),
                    ]);

                    // 🔁 Try Steadfast again with new session
                    $retrySteadfast = Http::withHeaders([
                        "cookie" => "steadfast_courier_session={$sessionCookie}",
                        "User-Agent" => 'Mozilla/5.0',
                        "Accept" => 'application/json, text/plain, */*'
                    ])->get("https://steadfast.com.bd/user/frauds/check/{$phone}");

                    $steadfast = $retrySteadfast->successful() ? $retrySteadfast->json() : null;
                }
            } catch (\Throwable $e) {
                $steadfast = null;
            }
        }

        $redx = $responses['redx']->json();
        $paperfly = $responses['paperfly']->json();

        $pathaoData = $pathao['data']['customer'] ?? ['total_delivery' => 0, 'successful_delivery' => 0];
        $steadfastData = [
            'total_delivered' => $steadfast['total_delivered'] ?? 0,
            'total_cancelled' => $steadfast['total_cancelled'] ?? 0,
        ];
        $redxData = $redx['data'] ?? ['totalParcels' => 0, 'deliveredParcels' => 0];
        $record = $paperfly['records'][0] ?? ['delivered' => 0, 'returned' => 0];

        $paperflyData = [
            'delivered' => (int) ($record['delivered'] ?? 0),
            'returned' => (int) ($record['returned'] ?? 0)
        ];

        $couriers = [
            ['total_parcel' => $pathaoData['total_delivery'], 'success_parcel' => $pathaoData['successful_delivery'], 'cancelled_parcel' => $pathaoData['total_delivery'] - $pathaoData['successful_delivery']],
            ['total_parcel' => $steadfastData['total_delivered'] + $steadfastData['total_cancelled'], 'success_parcel' => $steadfastData['total_delivered'], 'cancelled_parcel' => $steadfastData['total_cancelled']],
            ['total_parcel' => $redxData['totalParcels'], 'success_parcel' => $redxData['deliveredParcels'], 'cancelled_parcel' => $redxData['totalParcels'] - $redxData['deliveredParcels']],
            ['total_parcel' => $paperflyData['delivered'] + $paperflyData['returned'], 'success_parcel' => $paperflyData['delivered'], 'cancelled_parcel' => $paperflyData['returned']]
        ];

        return [
            'pathao' => [
                'total_parcel' => (int) $pathaoData['total_delivery'],
                'success_parcel' => (int) $pathaoData['successful_delivery'],
                'cancelled_parcel' => (int) $pathaoData['total_delivery'] - $pathaoData['successful_delivery'],
                'success_ratio' => (float) number_format($this->calculateSuccessRatio($pathaoData['successful_delivery'], $pathaoData['total_delivery']), 2)
            ],
            'steadfast' => [
                'total_parcel' => (int) $steadfastData['total_delivered'] + $steadfastData['total_cancelled'],
                'success_parcel' => (int) $steadfastData['total_delivered'],
                'cancelled_parcel' => (int) $steadfastData['total_cancelled'],
                'success_ratio' => (float) number_format($this->calculateSuccessRatio($steadfastData['total_delivered'], $steadfastData['total_delivered'] + $steadfastData['total_cancelled']), 2)
            ],
            'redx' => [
                'total_parcel' => (int) $redxData['totalParcels'],
                'success_parcel' => (int) $redxData['deliveredParcels'],
                'cancelled_parcel' => (int) $redxData['totalParcels'] - $redxData['deliveredParcels'],
                'success_ratio' => (float) number_format($this->calculateSuccessRatio($redxData['deliveredParcels'], $redxData['totalParcels']), 2)
            ],
            'paperfly' => [
                'total_parcel' => (int) $paperflyData['delivered'] + $paperflyData['returned'],
                'success_parcel' => (int) $paperflyData['delivered'],
                'cancelled_parcel' => (int) $paperflyData['returned'],
                'success_ratio' => (float) number_format($this->calculateSuccessRatio($paperflyData['delivered'], $paperflyData['delivered'] + $paperflyData['returned']), 2)
            ],
            'summary' => $this->calculateSummary($couriers)
        ];
    }

    private function calculateSuccessRatio($success, $total)
    {
        return $total > 0 ? round(($success / $total) * 100, 2) : 0;
    }

    private function calculateSummary($couriers)
    {
        $total = $success = $cancelled = 0;
        foreach ($couriers as $courier) {
            $total += $courier['total_parcel'];
            $success += $courier['success_parcel'];
            $cancelled += $courier['cancelled_parcel'];
        }
        return [
            'total_parcel' => $total,
            'success_parcel' => $success,
            'cancelled_parcel' => $cancelled,
            'success_ratio' => (float) number_format($this->calculateSuccessRatio($success, $total), 2)
        ];
    }
}
