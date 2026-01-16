Accommodations Model Overview
The accommodations system is structured around three main models: Hotel, Room, and RoomReservation. Here's a breakdown of each component:

1. Hotel Model
Purpose: Represents hotel properties available for accommodation
Key Attributes:
name: Name of the hotel
city: Location of the hotel
standing: Star rating/classification
description: Details about the hotel
Relationships:
Has many Room models
Has many HotelPhoto models (ordered by 'order' field)
2. Room Model
Purpose: Represents room types within hotels
Key Attributes:
type: Category/classification of the room
price: Cost per night
capacity: Maximum number of guests
total_rooms: Total rooms of this type
available_rooms: Currently available rooms
Relationships:
Belongs to a Hotel
Has many RoomReservation models
3. RoomReservation Model
Purpose: Tracks room bookings by delegations
Key Attributes:
rooms_reserved: Number of rooms booked
status: Current status of the reservation
is_cancelled: Boolean flag for cancellation
cancelled_at: When it was cancelled
check_in_date/check_out_date: Stay duration
number_of_nights: Calculated stay duration
internal_notes: Administrative notes
Relationships:
Belongs to a Room
Belongs to a Delegation
Has many Payment records
Key Features
Availability Tracking: Rooms track available inventory
Payment Integration: Supports both full and partial payments
Cancellation Handling: Tracks when and why reservations are cancelled
Photo Management: Hotels can have multiple photos in a specific order
Search & Filtering: Scopes for filtering hotels by availability, city, and star rating
The system is designed to manage accommodations for delegations participating in the African Championship in Yaoundé, with support for room reservations, payments, and administrative tracking.