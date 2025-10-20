<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pathao
        $tokens = [
            'pathao_api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiNzQ1MmM4MDFjMDA1Zjc3MDdjMTdiMTM3YmUzZjhjNTQ5ODBkZDZjZDAxMWQzMGUzM2MwMDYxYjgyODU0ZjgyOGViZmU0ZjgwNmE0NjU1ZGUiLCJpYXQiOjE3NTg3ODIyMDYuMjY3MTk4LCJuYmYiOjE3NTg3ODIyMDYuMjY3MjAxLCJleHAiOjE3NjY1NTgyMDYuMjUzMTk1LCJzdWIiOiIzMzU5ODYiLCJzY29wZXMiOltdfQ.qiJVHE0ozil5C2LqG-6jcFI17AFhl2eUgBNzoc3JufD07zKoVJ3nKxdqS4tGvS-jz8JQQ0JSS5kKzf6sCSrfpTJghepBzBOre6Tf5ldJNF0jaT6CdrCvZJ2b4CiJ-y4W-on0IAXgG214UoJmre7dmU3AQvx6LDtDdQ4bgFCv6eAMo2EbKFLPDLivEITISjSvtu7UMV9QZIgewxdGqEZ3YlWcaA5Ed9ZTzq8ZWKb3ckenft8aLf9dGLOx1YbvAu6zlC0j8FZvduhU4KKDvhKiWYrC-xHQ0oPKLAQYCqIC1R-bJDmC-UjnQXFHwV5d6dIIWAJdIDA0NS8XqwuMIWrJRtZf59ZOSPCD6pGUpuTN9UVoDWTirnlA5wOLe6x6CELkRmww9FbWRKZt39FHH87u-0P3z2cWNQs7x6HCaNkM_eA728UjO9zqsu7HAooMO4MzxxOhJ3F2hU0r-KP80JgmbNojgYESChwjRg8dlepwMcDfmKsrEwMDQV9TcCTFXc9rVj6YaZbUwJlJuuLY6fk4PO6dcjnqlSOTH0mcgKTvYilvItIJAvEC6bybJNKtkjS5zjaSkCaj8zy2UWlz33eSVYAmlW52IEtWgkFfqxRbIKY0Jclr5Iiqgyj6QMrXxbJI773KrDQ7tPevTrUyACY0PoHh-yfbqIF2PLGXPUnxbuw',
        ];
        foreach ($tokens as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Steadfast
        $tokens = [
            'steadfast_cookie' => 'steadfast_courier_session=eyJpdiI6ImJQRHVhYUlxQWJIZUowamhQeXhreWc9PSIsInZhbHVlIjoiaVFWNC9vUlBta3dJelk4UytMZnNwMUZ1anloUmNVZWU1RGxqSmtkemRyMGN3MU9lZlEvVDg3SGRDTDAzREYzZnJHUXpYWk9KM0NseURudlpOS0dHb0d6MXpFSTQzRUx5aTRzUmhlc2ROcWtlNktsWGRCZWhaMWFRaGJPbThzVjAiLCJtYWMiOiJlM2MxMzAyOGE4NDYxNDk5OTNjNWZkMmQ1MTEzODE0MDIyNzBhZThjMzdkOTU2ODVjZWFlOGY0OWJkMDVhY2I3IiwidGFnIjoiIn0%3D',
        ];
        foreach ($tokens as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // RedEX
        $tokens = [
            'redex_api_token' => '__ti__=s%3A78e82c18cf1298504241586d915d5abdbd0104b71c766950a18e49aa96e98c37fbd2e8c4eb6bc2effcb41d45e8bce3723e78840b6426c0368acfb9b118bc9050.iU6PrMpnY7FXOSy2ZExvbIY1vMTyfGyeVOG26pnVCe8',
        ];
        foreach ($tokens as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // PaperFly
        $tokens = [
            'paperfly_api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDc3MTY3MTQsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTc0NzcxNjcxNCwiZXhwIjoxNzc5Mjk5OTk5LCJ1c2VybmFtZSI6ImMxNzA1MTkiLCJkZXZpY2VJZGVudGlmaWVyIjoiZDY3YzViYjEtY2Y3MS0wY2M0LWU5NjgtMjgxNDgwNWJkZTM0In0.Er5hwq4uhe26E_PEy10kPnFOE7Mk-JeEeSKuDO-Gsik',
        ];
        foreach ($tokens as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
