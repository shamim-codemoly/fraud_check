# 📮 Courier Checking API Documentation

This API allows users to check the status and details of courier packages using multiple courier integrations (e.g., Sundarban, SA Paribahan, Janani, Pathao, etc.).

---

## 🛠️ Base URL


---

## 🔐 Authentication

- API is public, but rate-limited.
- For advanced usage, token-based auth can be added (e.g., Laravel Sanctum/Passport).

---

## 📬 Endpoint: Check Courier Status

### `GET /courier/check`

#### 🧾 Query Parameters:

| Parameter     | Type   | Required | Description                            |
|---------------|--------|----------|----------------------------------------|
| `tracking_no` | string | ✅ Yes    | The tracking number of the parcel      |
| `courier`     | string | ✅ Yes    | Courier name or alias (`janani`, `sundarban`, etc.) |

---

### 📥 Example Request:

```https
POST https://ck.codemoly.info/api/codemoly/fraud-check
{
    "api_key":"Y29kZW1vbHl8c2h5bW9saXxpdC1zZXJ2aWNlIHByb3ZpZGVyfHppai1iYW5haWNoZQ==",
    "phone": "01710000000"
}


{
    "pathao": {
        "total_parcel": 366,
        "success_parcel": 272,
        "cancelled_parcel": 94,
        "success_ratio": 74.32
    },
    "steadfast": {
        "total_parcel": 35,
        "success_parcel": 23,
        "cancelled_parcel": 12,
        "success_ratio": 65.71
    },
    "redx": {
        "total_parcel": 363,
        "success_parcel": 160,
        "cancelled_parcel": 203,
        "success_ratio": 44.08
    },
    "paperfly": {
        "total_parcel": 0,
        "success_parcel": 0,
        "cancelled_parcel": 0,
        "success_ratio": 0
    },
    "summary": {
        "total_parcel": 764,
        "success_parcel": 455,
        "cancelled_parcel": 309,
        "success_ratio": 59.55
    }
}
# fraud_check
