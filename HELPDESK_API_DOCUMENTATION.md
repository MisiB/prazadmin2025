# Helpdesk API Documentation

## Base URL
```
https://your-domain.test/api/helpdesk
```

## Authentication

Most endpoints are **public** and do not require authentication. However, some administrative endpoints require **Laravel Sanctum** token authentication.

### Protected Endpoints
These endpoints require a `Bearer` token in the Authorization header:
```
Authorization: Bearer YOUR_API_TOKEN
```

---

## Endpoints

### 1. Get Issue Groups

Retrieve all available issue groups.

**Endpoint:** `GET /api/helpdesk/issue-groups`  
**Authentication:** None  
**Method:** GET

#### Response (200 OK)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Technical Issues"
    },
    {
      "id": 2,
      "name": "Billing Issues"
    }
  ]
}
```

---

### 2. Get Issue Types

Retrieve all available issue types, optionally filtered by issue group.

**Endpoint:** `GET /api/helpdesk/issue-types`  
**Authentication:** None  
**Method:** GET

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| issuegroup_id | integer | No | Filter types by issue group |

#### Response (200 OK)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Network Connectivity",
      "issuegroup_id": 1,
      "department_id": 5
    },
    {
      "id": 2,
      "name": "Software Installation",
      "issuegroup_id": 1,
      "department_id": 5
    }
  ]
}
```

---

### 3. Create Ticket

Create a new support ticket.

**Endpoint:** `POST /api/helpdesk/tickets`  
**Authentication:** None  
**Method:** POST  
**Content-Type:** application/json

#### Request Body
```json
{
  "issuegroup_id": 1,
  "issuetype_id": 2,
  "title": "Cannot access email system",
  "description": "I am unable to log into my email account since this morning. I get an error message saying 'Invalid credentials' even though I'm using the correct password.",
  "priority": "High",
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "+260 977 123456",
  "regnumber": "EMP001",
  "department_id": 5,
  "attachments": []
}
```

#### Request Body Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| issuegroup_id | integer | Yes | Issue group ID |
| issuetype_id | integer | Yes | Issue type ID |
| title | string | Yes | Issue title (max 255 chars) |
| description | string | Yes | Detailed description |
| priority | string | Yes | Priority: "Low", "Medium", or "High" |
| name | string | Yes | Reporter's full name |
| email | string | Yes | Reporter's email address |
| phone | string | No | Reporter's phone number |
| regnumber | string | No | Reporter's registration/employee number |
| department_id | integer | No | Department to assign to |
| attachments | array | No | Array of base64 encoded images or URLs |

#### Response (201 Created)
```json
{
  "success": true,
  "message": "Issue ticket created successfully"
}
```

#### Error Response (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

---

### 4. Track Tickets by Email

Track all tickets submitted by a specific email address.

**Endpoint:** `POST /api/helpdesk/tickets/track`  
**Authentication:** None  
**Method:** POST  
**Content-Type:** application/json

#### Request Body
```json
{
  "email": "john.doe@example.com"
}
```

#### Response (200 OK)
```json
{
  "data": [
    {
      "id": 123,
      "ticket_number": "TKT-ABC123XY",
      "title": "Cannot access email system",
      "description": "I am unable to log into my email account...",
      "status": "in_progress",
      "priority": "High",
      "issue_group": {
        "id": 1,
        "name": "Technical Issues"
      },
      "issue_type": {
        "id": 2,
        "name": "Email Access"
      },
      "department": {
        "id": 5,
        "name": "IT Department"
      },
      "reporter": {
        "name": "John Doe",
        "email": "john.doe@example.com",
        "phone": "+260 977 123456",
        "registration_number": "EMP001"
      },
      "assignment": {
        "assigned_to": {
          "id": "9c8ce0e6-c173-4a9a-8483-0f9ad09d33b0",
          "name": "Jane Smith",
          "email": "jane.smith@company.com"
        },
        "assigned_by": {
          "id": "9c8ce0e6-c173-4a9a-8483-0f9ad09d33b1",
          "name": "IT Manager"
        },
        "assigned_at": "2025-10-16T14:30:00+00:00"
      },
      "attachments": [],
      "created_at": "2025-10-16T10:15:00+00:00",
      "updated_at": "2025-10-16T14:30:00+00:00",
      "created_by": {
        "id": "9c8ce0e6-c173-4a9a-8483-0f9ad09d33b0",
        "name": "John Doe"
      }
    }
  ],
  "meta": {
    "total": 1
  }
}
```

---

### 5. Get Ticket by Ticket Number

Retrieve a specific ticket using its ticket number.

**Endpoint:** `GET /api/helpdesk/tickets/number/{ticketNumber}`  
**Authentication:** None  
**Method:** GET

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| ticketNumber | string | Yes | Ticket number (e.g., TKT-ABC123XY) |

#### Example Request
```
GET /api/helpdesk/tickets/number/TKT-ABC123XY
```

#### Response (200 OK)
```json
{
  "id": 123,
  "ticket_number": "TKT-ABC123XY",
  "title": "Cannot access email system",
  "description": "I am unable to log into my email account...",
  "status": "in_progress",
  "priority": "High",
  "issue_group": {
    "id": 1,
    "name": "Technical Issues"
  },
  "issue_type": {
    "id": 2,
    "name": "Email Access"
  },
  "department": {
    "id": 5,
    "name": "IT Department"
  },
  "reporter": {
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+260 977 123456",
    "registration_number": "EMP001"
  },
  "assignment": {
    "assigned_to": {
      "id": "9c8ce0e6-c173-4a9a-8483-0f9ad09d33b0",
      "name": "Jane Smith",
      "email": "jane.smith@company.com"
    },
    "assigned_by": null,
    "assigned_at": null
  },
  "attachments": [],
  "created_at": "2025-10-16T10:15:00+00:00",
  "updated_at": "2025-10-16T10:15:00+00:00",
  "created_by": null
}
```

#### Error Response (404 Not Found)
```json
{
  "success": false,
  "message": "Ticket not found"
}
```

---

## Protected Endpoints (Require Authentication)

### 6. List All Tickets

Retrieve all tickets with optional filters.

**Endpoint:** `GET /api/helpdesk/tickets`  
**Authentication:** Required (Bearer Token)  
**Method:** GET

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status | string | No | Filter by status: open, in_progress, resolved, closed |
| priority | string | No | Filter by priority: Low, Medium, High |
| department_id | integer | No | Filter by department |
| issuegroup_id | integer | No | Filter by issue group |
| issuetype_id | integer | No | Filter by issue type |
| email | string | No | Filter by reporter email |

#### Example Request
```
GET /api/helpdesk/tickets?status=open&priority=High
Authorization: Bearer YOUR_API_TOKEN
```

#### Response (200 OK)
```json
{
  "data": [
    {
      "id": 123,
      "ticket_number": "TKT-ABC123XY",
      "title": "Cannot access email system",
      "status": "open",
      "priority": "High",
      "..."
    }
  ],
  "meta": {
    "total": 15
  }
}
```

---

### 7. Get Ticket by ID

Retrieve a specific ticket by its ID.

**Endpoint:** `GET /api/helpdesk/tickets/{id}`  
**Authentication:** Required (Bearer Token)  
**Method:** GET

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Ticket ID |

#### Example Request
```
GET /api/helpdesk/tickets/123
Authorization: Bearer YOUR_API_TOKEN
```

#### Response (200 OK)
Same structure as "Get Ticket by Ticket Number"

---

### 8. Update Ticket Status

Update the status of a ticket.

**Endpoint:** `PATCH /api/helpdesk/tickets/{id}/status`  
**Authentication:** Required (Bearer Token)  
**Method:** PATCH  
**Content-Type:** application/json

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Ticket ID |

#### Request Body
```json
{
  "status": "resolved"
}
```

#### Status Values
- `open` - Ticket is newly created
- `in_progress` - Ticket is being worked on
- `resolved` - Issue has been resolved (sends email notification)
- `closed` - Ticket is permanently closed

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Issue status updated successfully"
}
```

#### Error Response (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "The selected status is invalid."
}
```

---

### 9. Get Statistics

Retrieve overall ticket statistics.

**Endpoint:** `GET /api/helpdesk/statistics`  
**Authentication:** Required (Bearer Token)  
**Method:** GET

#### Example Request
```
GET /api/helpdesk/statistics
Authorization: Bearer YOUR_API_TOKEN
```

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "total": 250,
    "by_status": {
      "open": 45,
      "in_progress": 78,
      "resolved": 100,
      "closed": 27
    },
    "by_priority": {
      "low": 80,
      "medium": 120,
      "high": 50
    }
  }
}
```

---

## Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation error |
| 401 | Unauthorized - Invalid or missing authentication token |
| 500 | Internal Server Error - Server error |

---

## Error Handling

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    "field_name": ["Error description"]
  }
}
```

---

## Ticket Lifecycle

```
1. Created (status: open)
   ↓
2. Assigned to Department/User
   ↓
3. Work Started (status: in_progress)
   ↓
4. Issue Resolved (status: resolved) → Email sent to reporter
   ↓
5. Ticket Closed (status: closed)
```

---

## Integration Example (PHP/Laravel)

```php
use Illuminate\Support\Facades\Http;

// Create a ticket
$response = Http::post('https://your-domain.test/api/helpdesk/tickets', [
    'issuegroup_id' => 1,
    'issuetype_id' => 2,
    'title' => 'Cannot access email',
    'description' => 'Unable to log into email system',
    'priority' => 'High',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+260 977 123456',
]);

$ticketData = $response->json();

// Track tickets by email
$response = Http::post('https://your-domain.test/api/helpdesk/tickets/track', [
    'email' => 'john@example.com',
]);

$tickets = $response->json()['data'];

// Get ticket by ticket number (no auth required)
$response = Http::get('https://your-domain.test/api/helpdesk/tickets/number/TKT-ABC123XY');

$ticket = $response->json();

// Update ticket status (requires authentication)
$response = Http::withToken('YOUR_API_TOKEN')
    ->patch('https://your-domain.test/api/helpdesk/tickets/123/status', [
        'status' => 'resolved',
    ]);
```

---

## Notes

1. **Email Notifications**: When a ticket status is updated to `resolved`, an automatic email notification is sent to the reporter's email address.

2. **Ticket Numbers**: Ticket numbers are automatically generated in the format `TKT-XXXXXXXX` where X is a random alphanumeric character.

3. **Timestamps**: All timestamps are returned in ISO 8601 format (e.g., `2025-10-16T10:15:00+00:00`).

4. **Attachments**: Currently supports array of base64 encoded images or file URLs.

5. **Rate Limiting**: API endpoints may be rate-limited. Check response headers for rate limit information.

6. **CORS**: Ensure CORS is properly configured if calling from a different domain.

---

## Support

For API support or questions, please contact your system administrator.



















