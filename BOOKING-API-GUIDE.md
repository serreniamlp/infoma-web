# Booking API Documentation Guide

## Table of Contents

-   [Overview](#overview)
-   [Authentication](#authentication)
-   [API Endpoints](#api-endpoints)
-   [Flow Diagram](#flow-diagram)
-   [Models](#models)
-   [Error Handling](#error-handling)
-   [Examples](#examples)

## Overview

This documentation provides comprehensive information about the Booking API system for Infoma application. The API handles bookings for both residences and activities.

### Base URL

```
/api/v1/user/bookings
```

### Supported Formats

-   Request: JSON, Multipart Form Data (for file uploads)
-   Response: JSON

## Authentication

All endpoints require authentication using Laravel Sanctum.

```http
Authorization: Bearer <your-token>
```

## API Endpoints

### 1. Create Booking

Create a new booking for either residence or activity.

```http
POST /api/v1/user/bookings
```

#### Request Body

```json
{
    "bookable_type": "residence|activity",
    "bookable_id": "integer",
    "check_in_date": "YYYY-MM-DD",
    "check_out_date": "YYYY-MM-DD",  // Optional, for residence only
    "documents": [file1, file2, ...], // PDF, JPG, JPEG, PNG files
    "notes": "string"                 // Optional, max 1000 chars
}
```

#### Validation Rules

-   `bookable_type`: Must be either 'residence' or 'activity'
-   `bookable_id`: Must exist in respective table
-   `documents`:
    -   Required minimum 1 file
    -   Supported formats: PDF, JPG, JPEG, PNG
    -   Maximum size: 2MB per file
-   `check_in_date`:
    -   For residence: Must be today or future date
    -   For activity: Must match activity event_date
-   `check_out_date`:
    -   Required for residence only
    -   Must be after check_in_date

#### Success Response (201 Created)

```json
{
    "status": "success",
    "message": "Booking created successfully",
    "data": {
        "booking": {
            "id": 1,
            "booking_code": "BK-20250919-001",
            "status": "pending",
            "total_amount": 500000,
            "check_in_date": "2025-09-25",
            "check_out_date": "2025-09-27",
            "notes": "Butuh extra bed",
            "created_at": "2025-09-19T10:30:00Z",
            "bookable": {
                "id": 1,
                "name": "Deluxe Room 101",
                "type": "Residence",
                "price": 250000
            }
        }
    }
}
```

### 2. List Bookings

Retrieve paginated list of user's bookings.

```http
GET /api/v1/user/bookings
```

#### Query Parameters

-   `status`: Filter by booking status (optional, default: 'all')
-   `per_page`: Items per page (optional, default: 10)

#### Success Response (200 OK)

```json
{
    "status": "success",
    "data": {
        "bookings": {
            "data": [...],
            "pagination": {
                "current_page": 1,
                "last_page": 1,
                "per_page": 10,
                "total": 2,
                "from": 1,
                "to": 2
            }
        }
    }
}
```

### 3. Get Booking Detail

Retrieve detailed information about specific booking.

```http
GET /api/v1/user/bookings/{id}
```

#### Success Response (200 OK)

```json
{
    "status": "success",
    "data": {
        "booking": {
            "id": 1,
            "booking_code": "BK-20250919-001",
            "status": "approved",
            "total_amount": 500000,
            "check_in_date": "2025-09-25",
            "check_out_date": "2025-09-27",
            "notes": "Butuh extra bed",
            "rejection_reason": null,
            "created_at": "2025-09-19T10:30:00Z",
            "updated_at": "2025-09-19T11:00:00Z",
            "bookable": {...},
            "transaction": {...}
        }
    }
}
```

### 4. Update Booking

Update booking notes.

```http
PUT /api/v1/user/bookings/{id}
```

#### Request Body

```json
{
    "notes": "string" // Optional, max 1000 chars
}
```

#### Restrictions

-   Only available for bookings with status 'pending' or 'approved'
-   Only booking owner can update
-   Only notes field can be updated

#### Success Response (200 OK)

```json
{
    "status": "success",
    "message": "Booking updated successfully",
    "data": {
        "booking": {
            "id": 1,
            "booking_code": "BK-20250919-001",
            "status": "pending",
            "notes": "Updated notes",
            "updated_at": "2025-09-19T12:00:00Z"
        }
    }
}
```

### 5. Cancel Booking

Cancel an existing booking.

```http
POST /api/v1/user/bookings/{id}/cancel
```

#### Restrictions

-   Only available for bookings with status 'pending' or 'approved'
-   Only booking owner can cancel

#### Success Response (200 OK)

```json
{
    "status": "success",
    "message": "Booking cancelled successfully",
    "data": {
        "booking": {
            "id": 1,
            "booking_code": "BK-20250919-001",
            "status": "cancelled"
        }
    }
}
```

### 6. Get Payment Methods

Retrieve available payment methods for a booking.

```http
GET /api/v1/user/bookings/{id}/payment
```

#### Restrictions

-   Booking must be in 'approved' status
-   Payment must not be already completed

#### Success Response (200 OK)

```json
{
    "status": "success",
    "data": {
        "booking": {...},
        "payment_methods": {
            "bank_transfer": "Bank Transfer",
            "credit_card": "Credit Card",
            "e_wallet": "E-Wallet"
        }
    }
}
```

### 7. Process Payment

Submit payment for a booking.

```http
POST /api/v1/user/bookings/{id}/payment
```

#### Request Body

```json
{
    "payment_method": "bank_transfer|credit_card|e_wallet",
    "payment_proof": file  // Optional, image max 2MB
}
```

#### Success Response (200 OK)

```json
{
    "status": "success",
    "message": "Payment processed successfully",
    "data": {
        "booking": {
            "id": 1,
            "booking_code": "BK-20250919-001",
            "status": "approved",
            "transaction": {
                "id": 1,
                "payment_status": "pending",
                "payment_method": "bank_transfer",
                "final_amount": 500000
            }
        }
    }
}
```

## Flow Diagram

```
User                    System                      Admin
 |                        |                          |
 |-- Create Booking ----->|                          |
 |<-- Status: pending ----+                          |
 |                        |--- Notify Admin -------->|
 |                        |                          |
 |                        |<-- Review Booking -------|
 |<-- Status: approved ---+                          |
 |                        |                          |
 |-- Submit Payment ----->|                          |
 |<-- Payment Pending ----+                          |
 |                        |--- Verify Payment ------>|
 |                        |                          |
 |<-- Status: completed --+                          |
```

## Models

### Booking Status Flow

```
pending -> approved -> completed
       -> rejected
       -> cancelled
```

### Available Slots

-   System checks available slots before creating booking
-   For residence: Checks date range availability
-   For activity: Checks total slot availability

## Error Handling

### Common Error Responses

#### 400 Bad Request

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "field": ["Error message"]
    }
}
```

#### 403 Forbidden

```json
{
    "status": "error",
    "message": "Unauthorized access to this booking"
}
```

#### 404 Not Found

```json
{
    "status": "error",
    "message": "Booking not found"
}
```

#### 500 Internal Server Error

```json
{
    "status": "error",
    "message": "Failed to process request: {error_message}"
}
```

## Best Practices

1. Always check booking status before operations
2. Handle file uploads properly (check size and type)
3. Implement proper error handling
4. Use pagination for listing endpoints
5. Include proper authorization checks
6. Validate dates and availability
7. Handle concurrent bookings properly

## Rate Limiting

-   60 requests per minute per user
-   Applies to all endpoints
-   Status 429 returned if exceeded

## Changelog

### Version 1.0.0 (2025-09-19)

-   Initial release
-   Basic booking functionality
-   Payment integration
-   File upload support
