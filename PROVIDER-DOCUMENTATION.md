# Provider Documentation

## Overview
Provider is a user role in the Infoma system that manages residences and activities. Providers can create, update, and manage their listings, as well as handle bookings from users.

## Core Functions

### 1. Dashboard Management
- View overall statistics (residences, activities, bookings, revenue)
- Monitor monthly performance
- Track booking status distribution
- Access recent bookings and listings

### 2. Residence Management
- Create and manage residence listings
- Update residence details and availability
- Handle residence images
- Toggle residence active status
- Monitor residence bookings and ratings

### 3. Activity Management
- Create and manage activity listings
- Update activity details and schedules
- Handle activity images
- Toggle activity active status
- Monitor activity bookings and ratings

### 4. Booking Management
- View and manage incoming bookings
- Approve or reject booking requests
- Monitor booking status
- Handle booking notifications

## Process Flows

### 1. Residence Management Flow
1. Provider creates a new residence listing
2. Provides residence details (name, description, price, location, etc.)
3. Uploads residence images
4. Sets availability and capacity
5. Manages bookings for the residence
6. Updates residence details as needed
7. Can toggle residence status (active/inactive)

### 2. Activity Management Flow
1. Provider creates a new activity listing
2. Provides activity details (name, description, price, location, schedule)
3. Uploads activity images
4. Sets capacity and registration deadline
5. Manages activity bookings
6. Updates activity details as needed
7. Can toggle activity status (active/inactive)

### 3. Booking Management Flow
1. Receives booking request from user
2. Reviews booking details
3. Approves or rejects booking
4. If rejected, provides rejection reason
5. System sends notification to user
6. Manages booking status updates
7. Handles any booking-related communications

## API Documentation

### Authentication
All API endpoints require authentication using Sanctum token.
Header: `Authorization: Bearer {token}`

### Provider Dashboard Endpoints

#### Get Dashboard Data
```http
GET /api/v1/provider/dashboard
```
Returns:
- Total statistics
- Recent bookings
- Recent items
- Performance metrics

#### Get Charts Data
```http
GET /api/v1/provider/dashboard/charts?type={type}
```
Parameters:
- type: 'revenue' | 'bookings' | 'status'

### Residence Management Endpoints

#### List Residences
```http
GET /api/v1/provider/residences
```
Query Parameters:
- status: 'active' | 'inactive'
- category_id: number
- per_page: number

#### Create Residence
```http
POST /api/v1/provider/residences
```
Body:
```json
{
    "name": "string",
    "description": "string",
    "price_per_month": "number",
    "category_id": "number",
    "capacity": "number",
    "address": "string",
    "latitude": "number",
    "longitude": "number",
    "facilities": "array",
    "images": "file[]"
}
```

#### Update Residence
```http
PUT /api/v1/provider/residences/{residence}
```
Body: Same as create with optional fields

#### Toggle Residence Status
```http
PATCH /api/v1/provider/residences/{residence}/toggle-status
```

### Activity Management Endpoints

#### List Activities
```http
GET /api/v1/provider/activities
```
Query Parameters:
- status: 'active' | 'inactive'
- category_id: number
- per_page: number

#### Create Activity
```http
POST /api/v1/provider/activities
```
Body:
```json
{
    "name": "string",
    "description": "string",
    "price": "number",
    "category_id": "number",
    "capacity": "number",
    "location": "string",
    "latitude": "number",
    "longitude": "number",
    "event_date": "datetime",
    "registration_deadline": "datetime",
    "images": "file[]"
}
```

#### Update Activity
```http
PUT /api/v1/provider/activities/{activity}
```
Body: Same as create with optional fields

#### Toggle Activity Status
```http
PATCH /api/v1/provider/activities/{activity}/toggle-status
```

### Booking Management Endpoints

#### List Bookings
```http
GET /api/v1/provider/bookings
```
Query Parameters:
- status: 'all' | 'pending' | 'approved' | 'rejected'
- type: 'residence' | 'activity'
- search: string
- per_page: number

#### Get Booking Details
```http
GET /api/v1/provider/bookings/{booking}
```

#### Approve Booking
```http
PATCH /api/v1/provider/bookings/{booking}/approve
```
Body:
```json
{
    "notes": "string (optional)"
}
```

#### Reject Booking
```http
PATCH /api/v1/provider/bookings/{booking}/reject
```
Body:
```json
{
    "rejection_reason": "string",
    "notes": "string (optional)"
}
```

## Response Format
All API endpoints return responses in the following format:
```json
{
    "status": "success|error",
    "message": "string (optional)",
    "data": {
        // Response data specific to each endpoint
    }
}
```

## Error Handling
- 400: Bad Request - Invalid input
- 401: Unauthorized - Invalid or missing token
- 403: Forbidden - Insufficient permissions
- 404: Not Found - Resource not found
- 500: Server Error - Internal server error

## Notes
1. All date fields should be in ISO 8601 format
2. Image uploads should be in multipart/form-data format
3. Pagination is implemented on list endpoints
4. All monetary values are in IDR
5. Coordinates (latitude/longitude) should be valid decimal numbers