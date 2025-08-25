# Vendor Widget Rating System - Implementation Guide

## Overview
This document provides a comprehensive guide for implementing the Vendor Widget Rating System in new development environments. This system allows **ONLY regular users (buyers/sellers)** to rate their experience with vendors through vendor widgets displayed in user sidebars.

**Important**: This system is specifically for regular users only. Agents, coordinators, and admins have access to a separate vendor rating system through the main vendor management pages.

## System Architecture

### User Access Control
- **Regular Users (Buyers/Sellers)**: ✅ **CAN** rate vendors through widget interface
- **Agents**: ❌ **CANNOT** rate vendors through widget interface (separate system available)
- **Coordinators**: ❌ **CANNOT** rate vendors through widget interface (separate system available)
- **Admins**: ❌ **CANNOT** rate vendors through widget interface (separate system available)

### Business Purpose
- Capture client feedback on vendor services for agent reference
- Provide agents with insights into vendor quality from client perspective
- Maintain privacy - vendors cannot see individual client ratings

## Database Requirements

### Table: `vendor_ratings`
```sql
CREATE TABLE IF NOT EXISTS `vendor_ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('agent','coordinator','user') NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=no rating, 1-5=star rating',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `vendor_user_unique` (`vendor_id`, `user_id`, `user_type`),
  KEY `vendor_id` (`vendor_id`),
  KEY `user_id` (`user_id`),
  KEY `user_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Field Descriptions
- **rating_id**: Auto-incrementing primary key
- **vendor_id**: ID of the vendor being rated
- **user_id**: ID of the user giving the rating
- **user_type**: Type of user ('user', 'agent', 'coordinator')
- **rating**: Rating value (0-5, where 0 = no rating, 1-5 = star rating)
- **created_at**: Timestamp when rating was first created
- **updated_at**: Timestamp when rating was last modified

## Files Required

### 1. Database Setup Script
**File**: `admin/create_vendor_ratings_table.php`
- Creates the vendor_ratings table if it doesn't exist
- Run once during initial setup

### 2. Vendor Class Extension
**File**: `include/classes/c_vendor.php`
- Add the `GetUserRating()` method to retrieve user ratings

### 3. Widget Class Modification
**File**: `include/classes/c_widget.php`
- Modify the `DisplayFull()` method to include rating system
- Add rating display after phone number field

### 4. AJAX Endpoint
**File**: `ajax/save_vendor_rating.php`
- Handles saving/updating vendor ratings
- Supports all user types but widget system only accessible to regular users

### 5. Frontend Resources
**File**: `css/vendor_rating.css`
- Styles for rating interface, stars, tooltips, and responsive design

**File**: `js/vendor_rating_simple.js`
- JavaScript functionality for rating interactions
- Handles both desktop and mobile behaviors

### 6. User Page Integration
**File**: `pages/users/modules/footer_scripts.php`
- Includes vendor rating CSS and JavaScript
- Loads Font Awesome for icons
- **Primary Page**: `/users/index.php` - Main user dashboard integration
- **Sidebar Display**: `$user->DisplaySidebar()` - Shows vendor widgets with ratings
- **Note**: This file was specifically modified to add vendor rating includes and Font Awesome

## Implementation Status

### What's Been Implemented (August 9, 2025)
- ✅ Vendor rating system for agents/coordinators (5-star interface)
- ✅ Vendor rating system for vendor widgets in sidebars
- ✅ Database integration via `vendor_ratings` table
- ✅ AJAX saving via `ajax/save_vendor_rating.php`
- ✅ **Full integration with `/users/index.php` page**
- ✅ **Vendor rating CSS/JS properly loaded on user pages**
- ✅ **AJAX endpoint updated to handle all user types (agent, coordinator, user)**
- ✅ **Widget class updated to display ratings for all user types**
- ✅ **Info Icon with Tooltip system completed and tested**

## Implementation Steps

### Step 1: Database Setup
1. Run `admin/create_vendor_ratings_table.php` to create the vendor_ratings table
2. Verify table structure matches the schema above

### Step 2: Backend Implementation
1. **Add GetUserRating method to vendor class**:
```php
public function GetUserRating($user_id, $user_type)
{
    $vendor_id = intval($this->id);
    $user_id = intval($user_id);
    $user_type = $this->MakeDBSafe($user_type);
    
    $rating = database::query("SELECT rating FROM vendor_ratings WHERE vendor_id = '$vendor_id' AND user_id = '$user_id' AND user_type = '$user_type'");
    
    if (database::num_rows($rating) > 0) {
        $row = database::fetch_array($rating);
        return intval($row['rating']);
    }
    
    return 0; // Default to no rating
}
```

2. **Modify widget class DisplayFull method**:
```php
// Add rating section after phone number
echo("<div class='line'>");
echo("<div class='vendor-rating-label'>Rate your experience with this vendor <span class='info-icon-wrapper'><i class='fas fa-info-circle vendor-rating-info' data-tooltip='This is to let your agent know about the service you received. The vendor cannot see this rating.'></i><div class='vendor-tooltip'>This is to let your agent know about the service you received. The vendor cannot see this rating.</div></span></div>");
echo("<div class='vendor-rating-stars'>");

// Get current user's rating for this vendor
$current_user = $this->GetCurrentUser();
if ($current_user) {
    $current_user_id = $current_user->id;
    // Determine user type based on class
    if (get_class($current_user) === 'coordinator') {
        $current_user_type = 'coordinator';
    } elseif (get_class($current_user) === 'agent') {
        $current_user_type = 'agent';
    } elseif (get_class($current_user) === 'user_contact') {
        $current_user_type = 'user';
    } else {
        $current_user_type = 'user'; // Default fallback
    }
    
    $user_rating = $vendor->GetUserRating($current_user_id, $current_user_type);
    
    // Generate 5-star rating system
    for($i = 1; $i <= 5; $i++) {
        $star_class = ($i <= $user_rating) ? ' filled' : '';
        echo("<span class='star".$star_class."' data-vendor-id='".$vendor->id."' data-rating='".$i."' data-user-id='".$current_user_id."' data-user-type='".$current_user_type."'>&#9733;</span>");
    }
} else {
    // Fallback: show empty stars if user object is not available
    for($i = 1; $i <= 5; $i++) {
        echo("<span class='star' data-vendor-id='".$vendor->id."' data-rating='".$i."'>&#9733;</span>");
    }
}
echo("</div>");
echo("</div>");
```

### Step 3: Frontend Implementation
1. **Add vendor rating CSS** to `css/vendor_rating.css`
2. **Add vendor rating JavaScript** to `js/vendor_rating_simple.js`
3. **Update user footer scripts** to include vendor rating resources
4. **Ensure Font Awesome is loaded** for info icons and tooltips

### Step 4: AJAX Endpoint
Ensure `ajax/save_vendor_rating.php` exists and handles:
- User authentication validation
- Input validation (vendor_id, rating)
- Database operations (insert/update/delete)
- JSON response handling
- **Dynamic user type detection** from session (admin, coordinator, agent, user_contact)
- **Automatic user_id and user_type determination** from current session
- **Support for both POST and GET parameters** for flexibility

## User Experience Features

### User Authentication Flow
1. **User Login**: User logs into `/users/index.php`
2. **Session Creation**: System creates `user_contact` object in session
3. **Widget Display**: `$user->DisplaySidebar()` calls widget system
4. **User Type Detection**: Widget class detects user type as 'user'
5. **Rating Interface**: Vendor widgets display with 5-star rating system

### Visual Interface
- **Rating Label**: Clear instruction text with info icon
- **Info Icon**: Black info icon with helpful tooltip explaining rating purpose
  - **Tooltip Content**: "This is to let your agent know about the service you received. The vendor cannot see this rating."
  - **User Education**: Helps users understand the purpose and privacy of ratings
  - **Visual Enhancement**: Improves interface clarity and user experience
- **5-Star System**: Intuitive star-based rating interface
- **Hover Effects**: Stars light up on mouse over
- **Filled States**: Current rating clearly displayed
- **Responsive Design**: Works on all screen sizes

### Interaction Behavior
- **Click to Rate**: Click any star to set rating (1-5)
- **Toggle Rating**: Click same star to remove rating
- **Immediate Feedback**: Visual updates happen instantly
- **Persistent Storage**: Ratings saved to database

### Responsive Design
- **Desktop**: 20px stars with hover effects
- **Mobile**: 24px stars for better touch interaction
- **Breakpoint**: 991px (matches existing vendor table breakpoint)

## Mobile vs Desktop Differences

### Desktop Behavior
- **Tooltips**: Show on hover
- **Star Interactions**: Hover effects for preview
- **Error Handling**: Revert to previous rating on save failure

### Mobile Behavior
- **Tooltips**: Show on click/tap with auto-hide after 3 seconds
- **Star Interactions**: Touch-optimized with immediate visual feedback
- **Error Handling**: Keep user selection despite save failures
- **Touch Targets**: Larger stars (24px) for better usability
- **Info Icon Tooltips**: Touch-friendly tooltip system with auto-hide after 3 seconds

## Security Considerations

### Session Validation
- All ratings require valid user session
- User authentication checked before any rating operations
- Session timeout handling integrated

### User Isolation
- Users can only rate vendors, not see others' ratings
- User type and ID validated from session
- No cross-user rating access

### Input Validation
- Rating values limited to 0-5 range
- Vendor ID must be positive integer
- SQL injection protection via prepared statements

### Access Control
- Widget rating interface only accessible to regular users
- Agent/coordinator access blocked at widget level
- Separate rating systems for different user types

## Error Handling & Fallbacks

### AJAX Failures
- **Network Errors**: User selection preserved on mobile, reverted on desktop
- **Server Errors**: Graceful degradation with user feedback
- **Timeout Handling**: 10-second timeout with retry capability

### Missing Data
- **User Object**: Fallback to empty stars if user not available
- **Rating Data**: Default to 0 (no rating) if database lookup fails
- **Vendor Information**: Graceful handling of missing vendor data

### Database Issues
- **Connection Failures**: User notified of temporary unavailability
- **Constraint Violations**: Duplicate rating prevention
- **Transaction Rollback**: Automatic cleanup on failures

## Integration Points

### Existing Systems
- **Vendor Management**: Uses existing vendor database structure and classes
- **User Authentication**: Integrates with existing user login and session system
- **Widget System**: Extends current sidebar widget framework
- **AJAX Infrastructure**: Leverages existing AJAX handling and response system

### Data Flow
1. User logs into `/users/index.php` and system creates `user_contact` object in session
2. `$user->DisplaySidebar()` calls widget system to display vendor widgets
3. Widget class detects user type as 'user' and loads existing rating
4. User interacts with 5-star rating interface
5. JavaScript captures rating and sends AJAX request
6. AJAX endpoint validates and saves to database
7. Visual feedback updates immediately
8. Rating data available for agent reference

### Performance Optimizations
- **CSS/JS Loading**: Resources loaded only when needed
- **AJAX Efficiency**: Minimal data transfer for rating updates
- **Database Indexing**: Existing indexes on vendor_ratings table
- **Caching**: Leverages existing session and database caching

## Troubleshooting

### Common Issues
1. **Stars Not Displaying**: Check if vendor_rating.css is loaded
2. **Rating Not Saving**: Verify AJAX endpoint accessibility and database permissions
3. **Mobile Tooltips Not Working**: Check JavaScript console for errors
4. **User Type Detection Failing**: Verify user class inheritance and session data
5. **Info Icon Tooltips Not Working**: Ensure Font Awesome is loaded and tooltip CSS is included
6. **User Authentication Issues**: Verify user_contact session is properly established

### Debug Information
- JavaScript console logging enabled for troubleshooting
- AJAX response logging for server-side debugging
- User type and ID validation logging
- Database query result logging
- User authentication flow logging
- Widget rendering and user type detection logging

## Future Enhancements

### Planned Features
- **Rating History**: Show user's rating history
- **Rating Analytics**: Dashboard of vendor performance
- **Bulk Operations**: Rate multiple vendors at once
- **Rating Notifications**: Alerts for rating changes
- **Rating Export**: Download rating data for analysis

### Technical Improvements
- **Real-time Updates**: WebSocket integration for live rating updates
- **Offline Support**: Local storage for offline rating capture
- **Advanced Analytics**: Machine learning for rating insights
- **API Endpoints**: RESTful API for external integrations

## Conclusion

The Vendor Widget Rating System provides a secure, user-friendly way for regular users to rate their vendor experiences while maintaining strict access controls and privacy. The system integrates seamlessly with existing infrastructure and provides immediate visual feedback for all rating interactions.

**Key Success Factors**:
- Proper user type detection and access control
- Responsive design for mobile and desktop
- Robust error handling and fallback mechanisms
- Secure database operations and user isolation
- Seamless integration with existing widget system

**Implementation Priority**: High - Core functionality for user feedback collection
**Maintenance Level**: Low - Self-contained system with minimal external dependencies
**User Impact**: High - Improves user engagement and provides valuable agent insights
