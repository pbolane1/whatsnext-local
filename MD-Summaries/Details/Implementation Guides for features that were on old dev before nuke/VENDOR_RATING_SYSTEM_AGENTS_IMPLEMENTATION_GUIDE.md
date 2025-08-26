# Vendor Rating System for Agents - Implementation Guide

## Overview
This guide provides comprehensive, step-by-step instructions for implementing the Vendor Rating System for Agents in fresh installations. The system allows agents and coordinators to rate vendors on a 1-5 star scale, with different interfaces for desktop (popup) and mobile (inline stars), plus tooltip functionality.

## Feature Description
- **Purpose**: Allow agents/coordinators to rate vendors for personal reference
- **Rating Scale**: 1-5 stars (0 for no rating)
- **Desktop Interface**: Single star with number overlay + popup rating selector
- **Mobile Interface**: 5 inline stars for touch interaction
- **Tooltip Support**: Informational tooltips in rating columns
- **Database Storage**: All ratings saved to `vendor_ratings` table
- **User Types**: Supports agents and coordinators

## Implementation Steps

### Step 1: Database Setup

#### File: `sql/create_vendor_ratings_table.php`
Create this script to check and create the vendor_ratings table if needed:

```php
<?php
// Database setup script for vendor ratings
// Run this script to ensure the vendor_ratings table exists

require_once('../include/common.php');

echo "<h2>Vendor Ratings Table Setup</h2>";

// Check if table exists
$table_exists = false;
$result = database::query("SHOW TABLES LIKE 'vendor_ratings'");
if (database::num_rows($result) > 0) {
    $table_exists = true;
    echo "<p style='color: green;'>✅ vendor_ratings table already exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ vendor_ratings table does not exist - creating it now...</p>";
}

if (!$table_exists) {
    // Create the vendor_ratings table
    $sql = "
    CREATE TABLE `vendor_ratings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `vendor_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `user_type` varchar(20) NOT NULL,
        `rating` int(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_vendor_user` (`vendor_id`, `user_id`, `user_type`),
        KEY `vendor_id` (`vendor_id`),
        KEY `user_id` (`user_id`),
        KEY `user_type` (`user_type`),
        KEY `rating` (`rating`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        database::query($sql);
        echo "<p style='color: green;'>✅ vendor_ratings table created successfully</p>";
        
        // Verify table structure
        $result = database::query("DESCRIBE vendor_ratings");
        echo "<h3>Table Structure:</h3><ul>";
        while ($row = database::fetch_array($result)) {
            echo "<li><strong>{$row['Field']}</strong> - {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}</li>";
        }
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating table: " . $e->getMessage() . "</p>";
    }
} else {
    // Show existing table structure
    $result = database::query("DESCRIBE vendor_ratings");
    echo "<h3>Existing Table Structure:</h3><ul>";
    while ($row = database::fetch_array($result)) {
        echo "<li><strong>{$row['Field']}</strong> - {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}</li>";
    }
    echo "</ul>";
}

echo "<p><strong>Note:</strong> This script is safe to run multiple times - it will only create the table if it doesn't exist.</p>";
?>
```

### Step 2: Vendor Class Enhancement

#### File: `include/classes/c_vendor.php`
Add the GetUserRating method to the vendor class:

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

### Step 3: Transaction Handler Integration

#### File: `include/traits/t_transaction_handler.php`
Add the rating display to the ListVendors method. In the desktop table section:

```php
echo("<tr class='agent_bg_color1'>".$list->Header('Type of Vendor','').$list->Header('Name','vendor_title').$list->Header('Company','vendor_title').$list->Header('Email','vendor_title').$list->Header('Phone','vendor_title').$list->Header('Additional Info','vendor_title').$list->Header('My Rating <i class=\'fa fa-solid fa-circle-info\' data-toggle=\'tooltip\' title=\'Vendors and clients cannot see this rating. This is just for you and (if applicable) any coordinator linked to your account\'></i>').$list->Header('Delete')."</tr>");
```

And in the vendor row loop:

```php
echo("<td>");
// Get current user's rating for this vendor
$current_user_id = $this->id;
$current_user_type = $this->IsCoordinator() ? 'coordinator' : 'agent';
$user_rating = $vendor->GetUserRating($current_user_id, $current_user_type);

// Generate rating HTML with new star-with-number system
echo("<div class='vendor-rating-container' data-vendor-id='".$vendor->id."' data-current-rating='".$user_rating."'>");
echo("<div class='star-rating'>");
echo("<span class='star".($user_rating > 0 ? ' filled' : '')."'>★</span>");
echo("<span class='rating-number'>".($user_rating > 0 ? $user_rating : '')."</span>");
// Mobile inline stars (hidden on desktop)
echo("<div class='mobile-stars' style='display:none;'>");
for($i = 1; $i <= 5; $i++) {
    echo("<span data-value='".$i."' class='".($i <= $user_rating ? ' filled' : '')."'>★</span>");
}
echo("</div>");
echo("</div>");
echo("<div class='rating-popover'>");
echo("<div class='popover-stars'>");
echo("<span data-value='1'>★</span>");
echo("<span data-value='2'>★</span>");
echo("<span data-value='3'>★</span>");
echo("<span data-value='4'>★</span>");
echo("<span data-value='5'>★</span>");
echo("</div>");
echo("</div>");
echo("</div>");
echo("</td>");
```

For the mobile section, add the same rating HTML in the rating row:

```php
echo("<tr class='list_item'>");
echo("<td>Rating <i class='fa fa-info-circle rating-info-icon' data-toggle='tooltip' title='Vendors and clients cannot see this rating. This is just for you and (if applicable) any coordinator linked to your account.'></i></td><td>");
// Same rating HTML as desktop
echo("</td>");
echo("</tr>");
```

### Step 4: CSS Styling

#### File: `css/vendor_rating.css`
Create the complete CSS file:

```css
/* Vendor Rating System Styles */
.vendor-rating-container {
    position: relative;
    display: inline-block;
    transition: all 0.2s ease-in-out;
}

.vendor-rating-container:hover {
    border-color: #007cba;
}

.vendor-rating-container.popup-open {
    border-color: #28a745;
}

.star-rating {
    position: relative;
    display: inline-block;
}

.star-rating .star {
    font-size: 30px;
    color: #ccc;
    cursor: pointer;
    transition: color 0.15s ease-in-out;
    position: relative;
}

.star-rating .star.filled {
    color: gold;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

.star-rating .rating-number {
    position: absolute;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    color: #333;
    font-weight: bold;
    min-width: 12px;
    text-align: center;
    pointer-events: none;
    z-index: 2;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

.star-rating .rating-number:not(:empty) {
    opacity: 1;
}

/* Mobile stars - hidden by default on desktop */
.star-rating .mobile-stars {
    display: none;
}

.rating-popover {
    position: absolute;
    top: 50px;
    left: 0;
    background: white;
    border: 2px solid #333;
    padding: 10px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: none;
    z-index: 99999;
    min-width: 200px;
    overflow: visible;
}

.rating-popover.show {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.rating-popover .popover-stars {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.rating-popover .popover-stars span {
    font-size: 24px;
    cursor: pointer;
    color: #ccc;
    padding: 4px;
    transition: color 0.15s ease-in-out;
    user-select: none;
    border-radius: 3px;
}

.rating-popover .popover-stars span:hover {
    color: gold;
}

.rating-popover .popover-stars span.hover,
.rating-popover .popover-stars span.selected {
    color: gold;
}

.rating-popover .popover-stars span.selected {
    text-shadow: 0 0 8px rgba(255, 215, 0, 0.6);
}

/* Ensure table cells don't clip the popup */
td {
    position: relative;
    overflow: visible;
}

/* Ensure table doesn't clip the popup */
table {
    overflow: visible;
}

/* Ensure any parent containers don't clip the popup */
.vendor-rating-container * {
    overflow: visible;
}

/* Mobile responsive adjustments */
@media (max-width: 767px) {
    .rating-popover {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000000;
        max-width: 90vw;
        min-width: 200px;
    }
    
    .rating-popover .popover-stars span {
        font-size: 28px;
        padding: 6px;
    }
}

/* Mobile inline rating system (991px and below) */
@media (max-width: 991px) {
    /* Hide the single star and popup system on mobile */
    .star-rating .star,
    .star-rating .rating-number,
    .rating-popover {
        display: none !important;
    }
    
    /* Show the mobile inline stars */
    .star-rating .mobile-stars {
        display: flex !important;
        gap: 8px;
        align-items: center;
    }
    
    .star-rating .mobile-stars span {
        font-size: 32px;
        color: #ccc;
        cursor: pointer;
        transition: color 0.15s ease-in-out;
        user-select: none;
        padding: 4px;
    }
    
    .star-rating .mobile-stars span:hover {
        color: gold;
    }
    
    .star-rating .mobile-stars span.filled {
        color: gold;
        text-shadow: 0 0 8px rgba(255, 215, 0, 0.6);
    }
    
    .star-rating .mobile-stars span.hover {
        color: gold;
    }
    
    /* Info icon styling */
    .rating-info-icon {
        display: inline-block;
        margin-left: 8px;
        color: #000000;
        cursor: help;
        font-size: 16px;
    }
    
    .rating-info-icon:hover {
        color: #333333;
    }
}
```

### Step 5: JavaScript Functionality

#### File: `js/vendor_rating.js`
Create the complete JavaScript file:

```javascript
// Vendor Rating System - Production Version
(function() {
    'use strict';
    
    function init() {
        bindEvents();
        initializeRatings();
    }

    function bindEvents() {
        // Handle tooltip functionality for mobile
        bindTooltipEvents();
        
        // Handle clicks on rating containers
        $('.vendor-rating-container').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const $container = $(this);
            const $popup = $container.find('.rating-popover');
            const isOpen = $popup.hasClass('show');
            
            // Close all other popups first
            $('.rating-popover').removeClass('show');
            $('.vendor-rating-container').removeClass('popup-open');
            
            if (!isOpen) {
                $popup.addClass('show');
                $container.addClass('popup-open');
                updateStarSelection($container);
            }
        });

        // Handle clicks on popup stars
        $('.rating-popover .popover-stars span').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const $star = $(this);
            const $container = $star.closest('.vendor-rating-container');
            const rating = parseInt($star.data('value'));
            
            // Process the rating
            selectRating($container, rating);
            
            // Close popup immediately
            $container.find('.rating-popover').removeClass('show');
            $container.removeClass('popup-open');
        });

        // Handle clicks on mobile stars (991px and below)
        $('.mobile-stars span').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const $star = $(this);
            const $container = $star.closest('.vendor-rating-container');
            const rating = parseInt($star.data('value'));
            
            // Process the rating
            selectRating($container, rating);
        });

        // Close popups when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.vendor-rating-container').length) {
                $('.rating-popover').removeClass('show');
                $('.vendor-rating-container').removeClass('popup-open');
            }
        });

        // Handle popup star hover effects
        $('.rating-popover .popover-stars span').on('mouseenter', function() {
            const $star = $(this);
            const rating = parseInt($star.data('value'));
            const $container = $star.closest('.vendor-rating-container');
            
            // Show hover effect
            $container.find('.popover-stars span').removeClass('hover');
            for (let i = 1; i <= rating; i++) {
                $container.find(`.popover-stars span[data-value="${i}"]`).addClass('hover');
            }
        });

        $('.rating-popover .popover-stars span').on('mouseleave', function() {
            const $container = $(this).closest('.vendor-rating-container');
            $container.find('.popover-stars span').removeClass('hover');
        });

        // Handle mobile star hover effects
        $('.mobile-stars span').on('mouseenter', function() {
            const $star = $(this);
            const rating = parseInt($star.data('value'));
            const $container = $star.closest('.vendor-rating-container');
            
            // Show hover effect
            $container.find('.mobile-stars span').removeClass('hover');
            for (let i = 1; i <= rating; i++) {
                $container.find(`.mobile-stars span[data-value="${i}"]`).addClass('hover');
            }
        });

        $('.mobile-stars span').on('mouseleave', function() {
            const $container = $(this).closest('.vendor-rating-container');
            $container.find('.mobile-stars span').removeClass('hover');
        });
    }

    function bindTooltipEvents() {
        // Initialize Bootstrap tooltips if available
        if (typeof $.fn.tooltip === 'function') {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    function initializeRatings() {
        // Set initial star states based on data attributes
        $('.vendor-rating-container').each(function() {
            const $container = $(this);
            const currentRating = parseInt($container.data('current-rating'));
            
            if (currentRating > 0) {
                updateDisplay($container, currentRating);
            }
        });
    }

    function updateStarSelection($container) {
        const currentRating = parseInt($container.data('current-rating'));
        
        // Update popup stars
        $container.find('.popover-stars span').removeClass('selected');
        if (currentRating > 0) {
            for (let i = 1; i <= currentRating; i++) {
                $container.find(`.popover-stars span[data-value="${i}"]`).addClass('selected');
            }
        }
    }

    function selectRating($container, rating) {
        const vendorId = $container.data('vendor-id');
        const currentRating = parseInt($container.data('current-rating'));
        
        // Toggle rating if same value clicked
        if (rating === currentRating) {
            rating = 0; // Remove rating
        }
        
        // Save rating to database
        saveRating(vendorId, rating, $container);
    }

    function saveRating(vendorId, rating, $container) {
        // Show loading state
        $container.addClass('saving');
        
        $.ajax({
            url: '/ajax/save_vendor_rating.php',
            method: 'POST',
            data: {
                vendor_id: vendorId,
                rating: rating
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update display
                    updateDisplay($container, rating);
                    $container.data('current-rating', rating);
                    
                    // Show success feedback
                    showRatingFeedback($container, rating, true);
                } else {
                    // Show error feedback
                    showRatingFeedback($container, rating, false, response.message);
                }
            },
            error: function() {
                // Show error feedback
                showRatingFeedback($container, rating, false, 'Network error occurred');
            },
            complete: function() {
                $container.removeClass('saving');
            }
        });
    }

    function updateDisplay($container, rating) {
        // Update desktop star
        const $star = $container.find('.star');
        const $ratingNumber = $container.find('.rating-number');
        
        if (rating > 0) {
            $star.addClass('filled');
            $ratingNumber.text(rating);
        } else {
            $star.removeClass('filled');
            $ratingNumber.text('');
        }
        
        // Update mobile stars
        $container.find('.mobile-stars span').removeClass('filled');
        for (let i = 1; i <= rating; i++) {
            $container.find(`.mobile-stars span[data-value="${i}"]`).addClass('filled');
        }
    }

    function showRatingFeedback($container, rating, success, message) {
        // Create feedback element
        const $feedback = $('<div class="rating-feedback"></div>');
        $feedback.text(success ? `Rating ${rating > 0 ? 'saved' : 'removed'} successfully` : (message || 'Error saving rating'));
        $feedback.addClass(success ? 'success' : 'error');
        
        // Position feedback
        $feedback.css({
            position: 'absolute',
            top: '-30px',
            left: '50%',
            transform: 'translateX(-50%)',
            background: success ? '#28a745' : '#dc3545',
            color: 'white',
            padding: '5px 10px',
            borderRadius: '3px',
            fontSize: '12px',
            zIndex: 1000,
            whiteSpace: 'nowrap'
        });
        
        // Add to container
        $container.append($feedback);
        
        // Remove after delay
        setTimeout(function() {
            $feedback.remove();
        }, 3000);
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        init();
    });
})();
```

### Step 6: AJAX Endpoint

#### File: `ajax/save_vendor_rating.php`
Create the rating save endpoint:

```php
<?php
// Start output buffering to prevent any HTML output before session handling
ob_start();

$__AJAX__=1;

// Fix the include path to use absolute path
$base_path = dirname(dirname(__FILE__)); // 2 levels up for ajax files
include($base_path . '/include/common.php');

// Check if user is logged in
$user_id = null;
$user_type = null;

if (Session::Get('admin_id')) {
    $user_id = Session::Get('admin_id');
    $user_type = 'admin';
} elseif (Session::Get('coordinator_id')) {
    $user_id = Session::Get('coordinator_id');
    $user_type = 'coordinator';
} elseif (Session::Get('agent_id')) {
    $user_id = Session::Get('agent_id');
    $user_type = 'agent';
} else {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'User not logged in'));
    exit;
}

// Validate input
$vendor_id = intval($_POST['vendor_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);

if ($vendor_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid vendor ID'));
    exit;
}

if ($rating < 0 || $rating > 5) {
    echo json_encode(array('success' => false, 'message' => 'Invalid rating value'));
    exit;
}

try {
    if ($rating == 0) {
        // Remove rating
        $sql = "DELETE FROM vendor_ratings WHERE vendor_id = ? AND user_id = ? AND user_type = ?";
        $stmt = database::prepare($sql);
        $stmt->bind_param('iis', $vendor_id, $user_id, $user_type);
        $result = $stmt->execute();
    } else {
        // Insert or update rating
        $sql = "INSERT INTO vendor_ratings (vendor_id, user_id, user_type, rating) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating = ?";
        $stmt = database::prepare($sql);
        $stmt->bind_param('iisii', $vendor_id, $user_id, $user_type, $rating, $rating);
        $result = $stmt->execute();
    }
    
    if ($result) {
        echo json_encode(array('success' => true, 'message' => 'Rating saved successfully'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Database error'));
    }
    
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
}
?>
```

### Step 7: Page Integration

#### File: `pages/agents/vendors.php`
Add the CSS and JavaScript includes:

```php
<head>
    <title>Agents - Vendors</title>
    <?php include($base_path . '/modules/head.php');?>
    <?php include('modules/head.php');?>
    <link rel="stylesheet" href="../../css/vendor_rating.css">
</head>
```

And in the footer:

```php
<script src="../../js/vendor_rating.js"></script>
```

#### File: `pages/coordinators/vendors.php`
Add the same includes for coordinator pages.

## Testing and Validation

### Database Verification
1. Run the database setup script
2. Verify `vendor_ratings` table exists with correct structure
3. Check that ratings can be saved and retrieved

### Functionality Testing
1. **Desktop Testing**: Click star to open popup, select rating, verify save
2. **Mobile Testing**: Click inline stars, verify rating updates
3. **Tooltip Testing**: Hover over info icons, verify tooltip content
4. **Rating Toggle**: Click same rating twice, verify removal

### Cross-User Testing
1. Test with agent accounts
2. Test with coordinator accounts
3. Verify ratings are isolated per user

## File Checklist
- [ ] `sql/create_vendor_ratings_table.php` - Database setup script
- [ ] `include/classes/c_vendor.php` - GetUserRating method added
- [ ] `include/traits/t_transaction_handler.php` - Rating display integration
- [ ] `css/vendor_rating.css` - Complete styling
- [ ] `js/vendor_rating.js` - Rating functionality
- [ ] `ajax/save_vendor_rating.php` - Rating save endpoint
- [ ] `pages/agents/vendors.php` - CSS/JS includes
- [ ] `pages/coordinators/vendors.php` - CSS/JS includes

## Implementation Time
- **Database Setup**: 10-15 minutes
- **PHP Implementation**: 20-25 minutes
- **CSS Styling**: 15-20 minutes
- **JavaScript**: 25-30 minutes
- **Integration**: 15-20 minutes
- **Testing**: 20-30 minutes
- **Total**: 105-140 minutes

---

**Last Updated**: January 8, 2025  
**Implementation Status**: ✅ Complete and Tested  
**Browser Support**: Modern browsers with full feature support  
**User Types Supported**: Agents and Coordinators  
**Rating Scale**: 1-5 stars (0 for no rating)  
**Responsive Design**: Desktop popup + Mobile inline stars
