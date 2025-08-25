# COLOR PRESETS IMPLEMENTATION GUIDE

## Overview
This guide provides a comprehensive implementation of the Color Presets feature for agents, coordinators, and admin users. The system provides 29 pre-configured color combinations across two categories with responsive design for desktop, tablet, and mobile devices.

## Table of Contents
1. [System Architecture](#system-architecture)
2. [File Structure](#file-structure)
3. [Configuration System](#configuration-system)
4. [Core Implementation](#core-implementation)
5. [Responsive Design](#responsive-design)
6. [Performance Optimizations](#performance-optimizations)
7. [Accessibility Features](#accessibility-features)
8. [Integration Points](#integration-points)
9. [Admin Implementation](#admin-implementation)
10. [Testing & Validation](#testing--validation)

## System Architecture

### Design Principles
- **Modular**: Reusable components across different user types
- **Responsive**: Optimized for desktop, tablet, and mobile
- **Accessible**: WCAG compliant with keyboard navigation and screen reader support
- **Performance**: Lazy loading and optimized rendering
- **Maintainable**: Clean separation of concerns

### Core Components
- **Color Presets Manager**: Handles preset loading, validation, and application
- **Responsive Layout Engine**: Manages different screen size layouts
- **Preview System**: Real-time timeline preview with selected colors
- **Color Picker Integration**: Seamless integration with existing color picker system

## File Structure

```
include/
├── config/
│   └── color_presets.json          # Preset configuration
├── classes/
│   ├── c_agent.php                 # Agent implementation
│   ├── c_coordinator.php           # Coordinator implementation
│   └── c_admin.php                 # Admin implementation (future)
├── traits/
│   └── t_color_presets.php         # Shared color presets functionality
└── lib/
    └── color_presets_manager.php   # Core color presets logic

public/
├── css/
│   └── color_presets.css           # Dedicated CSS for color presets
└── js/
    ├── color_presets.js            # Core JavaScript functionality
    ├── color_presets_responsive.js # Responsive behavior
    └── color_presets_accessibility.js # Accessibility features

components/
└── color-picker.php                # Reusable color picker component
```

## Configuration System

### JSON Configuration File
**File**: `include/config/color_presets.json`

```json
{
  "area1": [
    {
      "name": "Black & Gold",
      "c1": "#040404",
      "c1fg": "#FFFFFF",
      "c2": "#edb732",
      "c2fg": "#040404"
    }
  ],
  "area2": [
    {
      "name": "Black & Pink",
      "c1": "#1c1c19",
      "c1fg": "#FFFFFF",
      "c2": "#ce4881",
      "c2fg": "#FFFFFF"
    }
  ]
}
```

### Fallback System
- Primary: JSON configuration file
- Secondary: Hardcoded presets in PHP
- Tertiary: Default color values

## Core Implementation

### 1. Shared Trait Implementation
**File**: `include/traits/t_color_presets.php`

```php
<?php
trait t_color_presets {
    
    /**
     * Load color presets from configuration
     * @return array Array of preset data
     */
    protected function loadColorPresets() {
        $presets = array('area1' => null, 'area2' => null);
        $json_file = _navigation::GetBasePath() . '/include/config/color_presets.json';
        
        if (file_exists($json_file)) {
            $json_data = @file_get_contents($json_file);
            $data = @json_decode($json_data, true);
            if (is_array($data)) {
                if (isset($data['area1']) && is_array($data['area1'])) {
                    $presets['area1'] = $data['area1'];
                }
                if (isset($data['area2']) && is_array($data['area2'])) {
                    $presets['area2'] = $data['area2'];
                }
            }
        }
        
        // Fallback to hardcoded presets
        if (!$presets['area1']) {
            $presets['area1'] = $this->getHardcodedPresetsArea1();
        }
        if (!$presets['area2']) {
            $presets['area2'] = $this->getHardcodedPresetsArea2();
        }
        
        return $presets;
    }
    
    /**
     * Generate color presets HTML
     * @param string $prefix Unique prefix for IDs and classes
     * @param array $presets Preset data
     * @return string HTML output
     */
    protected function generateColorPresetsHTML($prefix, $presets) {
        $html = '';
        
        // Area 1: Popular Brokerage Presets
        $html .= $this->generatePresetArea($prefix . '_area1', 'Popular Brokerage Presets', $presets['area1']);
        
        // Area 2: Other Color Combinations
        $html .= $this->generatePresetArea($prefix . '_area2', 'Other Color Combinations', $presets['area2']);
        
        return $html;
    }
    
    /**
     * Generate preset area HTML
     * @param string $id Unique identifier
     * @param string $title Area title
     * @param array $presets Preset data
     * @return string HTML output
     */
    private function generatePresetArea($id, $title, $presets) {
        $html = '<div class="line" style="margin-top:10px;">';
        $html .= '<p>&nbsp;</p><p><b>Or Choose from a selection of Color Presets:</b></p>';
        $html .= '<a href="#" id="' . $id . '_link"><span class="preset-toggle-icon">+</span>' . htmlspecialchars($title) . '</a>';
        $html .= '</div>';
        
        $html .= '<div id="' . $id . '" style="display:none;margin:8px 0 12px 0;">';
        
        foreach ($presets as $preset) {
            $html .= $this->generatePresetRow($preset);
        }
        
        // Disclaimer for Area 1
        if (strpos($title, 'Popular Brokerage') !== false) {
            $html .= '<div class="preset-disclaimer" style="font-size:12px;color:#666;margin-top:8px;">';
            $html .= 'These presets are for convenience only. What\'s Next App, LLC. is not affiliated with or endorsed by any of the companies whose branding may be visually similar.';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate individual preset row
     * @param array $preset Preset data
     * @return string HTML output
     */
    private function generatePresetRow($preset) {
        $html = '<div class="preset-row" ';
        $html .= 'data-c1="' . htmlspecialchars($preset['c1']) . '" ';
        $html .= 'data-c1fg="' . htmlspecialchars($preset['c1fg']) . '" ';
        $html .= 'data-c2="' . htmlspecialchars($preset['c2']) . '" ';
        $html .= 'data-c2fg="' . htmlspecialchars($preset['c2fg']) . '" ';
        $html .= 'style="cursor:pointer;display:flex;align-items:center;padding:6px 4px;border-bottom:1px solid #eee;">';
        
        $html .= '<div class="preset-name" style="flex:1;font-weight:500;display:flex;align-items:center;gap:8px;">';
        $html .= htmlspecialchars($preset['name']);
        $html .= '<span class="preset-selected-badge">Selected</span>';
        $html .= '</div>';
        
        $html .= '<div class="preset-swatches" style="display:flex;gap:6px;">';
        $html .= '<span class="preset-swatch" style="width:15px;height:15px;display:inline-block;border:1px solid #ccc;background:' . $preset['c1'] . '"></span>';
        $html .= '<span class="preset-swatch" style="width:15px;height:15px;display:inline-block;border:1px solid #ccc;background:' . $preset['c2'] . '"></span>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get hardcoded presets for Area 1
     * @return array Preset data
     */
    private function getHardcodedPresetsArea1() {
        return array(
            array('name'=>'Black & Gold','c1'=>'#040404','c1fg'=>'#FFFFFF','c2'=>'#edb732','c2fg'=>'#040404'),
            array('name'=>'Black & Gold 2','c1'=>'#000000','c1fg'=>'#c9ae64','c2'=>'#c9ae64','c2fg'=>'#000000'),
            array('name'=>'Black & Green','c1'=>'#000000','c1fg'=>'#FFFFFF','c2'=>'#5eba48','c2fg'=>'#000000'),
            array('name'=>'Black & Orange','c1'=>'#19282f','c1fg'=>'#FFFFFF','c2'=>'#fdaf14','c2fg'=>'#19282f'),
            array('name'=>'Black & White','c1'=>'#000000','c1fg'=>'#FFFFFF','c2'=>'#FFFFFF','c2fg'=>'#000000'),
            array('name'=>'Blue & Orange','c1'=>'#214c9f','c1fg'=>'#FFFFFF','c2'=>'#f58527','c2fg'=>'#FFFFFF'),
            array('name'=>'Blue & White','c1'=>'#012169','c1fg'=>'#FFFFFF','c2'=>'#FFFFFF','c2fg'=>'#012169'),
            array('name'=>'Navy & White','c1'=>'#042c50','c1fg'=>'#FFFFFF','c2'=>'#FFFFFF','c2fg'=>'#042c50'),
            array('name'=>'Purple & White','c1'=>'#6d043f','c1fg'=>'#FFFFFF','c2'=>'#FFFFFF','c2fg'=>'#6d043f'),
            array('name'=>'Red & Blue','c1'=>'#c80f2e','c1fg'=>'#FFFFFF','c2'=>'#0b3279','c2fg'=>'#FFFFFF'),
            array('name'=>'Red & Blue 2','c1'=>'#df2130','c1fg'=>'#FFFFFF','c2'=>'#0441ab','c2fg'=>'#FFFFFF'),
            array('name'=>'Red & Gray','c1'=>'#cf0828','c1fg'=>'#FFFFFF','c2'=>'#545456','c2fg'=>'#FFFFFF'),
            array('name'=>'Tan & Blue','c1'=>'#d4b98c','c1fg'=>'#FFFFFF','c2'=>'#002341','c2fg'=>'#FFFFFF'),
        );
    }
    
    /**
     * Get hardcoded presets for Area 2
     * @return array Preset data
     */
    private function getHardcodedPresetsArea2() {
        return array(
            array('name'=>'Black & Pink','c1'=>'#1c1c19','c1fg'=>'#FFFFFF','c2'=>'#ce4881','c2fg'=>'#FFFFFF'),
            array('name'=>'Black & Tomato','c1'=>'#2e2a26','c1fg'=>'#FFFFFF','c2'=>'#ea4c3e','c2fg'=>'#FFFFFF'),
            array('name'=>'Blue & Gray','c1'=>'#194f8f','c1fg'=>'#FFFFFF','c2'=>'#a2a2a0','c2fg'=>'#000000'),
            array('name'=>'Dark Red & Light Pink','c1'=>'#9a0011','c1fg'=>'#fdf6f4','c2'=>'#fdf6f4','c2fg'=>'#9a0011'),
            array('name'=>'Gold & Blue','c1'=>'#fbd762','c1fg'=>'#000000','c2'=>'#01539e','c2fg'=>'#FFFFFF'),
            array('name'=>'Light & Dark Tan','c1'=>'#f3edd7','c1fg'=>'#755139','c2'=>'#755139','c2fg'=>'#f3edd7'),
            array('name'=>'Light Blue & Light Pink','c1'=>'#88abe4','c1fg'=>'#FFFFFF','c2'=>'#fdf6f4','c2fg'=>'#000000'),
            array('name'=>'Light Pink & Blue','c1'=>'#faebee','c1fg'=>'#000000','c2'=>'#343d79','c2fg'=>'#FFFFFF'),
            array('name'=>'Mango & Moss','c1'=>'#daa03d','c1fg'=>'#FFFFFF','c2'=>'#616149','c2fg'=>'#FFFFFF'),
            array('name'=>'Navy & Mint','c1'=>'#06213f','c1fg'=>'#FFFFFF','c2'=>'#abf0d1','c2fg'=>'#000000'),
            array('name'=>'Orange & White','c1'=>'#f55800','c1fg'=>'#FFFFFF','c2'=>'#FFFFFF','c2fg'=>'#f55800'),
            array('name'=>'Pale Blue & Grey','c1'=>'#f1f3ff','c1fg'=>'#000000','c2'=>'#a2a2a0','c2fg'=>'#000000'),
            array('name'=>'Purple & Ice','c1'=>'#604081','c1fg'=>'#FFFFFF','c2'=>'#c8d3d4','c2fg'=>'#000000'),
            array('name'=>'Sage & Purple','c1'=>'#cace91','c1fg'=>'#000000','c2'=>'#78518c','c2fg'=>'#FFFFFF'),
            array('name'=>'Tan & Eggplant','c1'=>'#d9c39e','c1fg'=>'#000000','c2'=>'#3b3659','c2fg'=>'#FFFFFF'),
            array('name'=>'Two Blue','c1'=>'#2161b1','c1fg'=>'#FFFFFF','c2'=>'#9cc3d4','c2fg'=>'#000000'),
            array('name'=>'Two Green','c1'=>'#2c5f2e','c1fg'=>'#FFFFFF','c2'=>'#98bd63','c2fg'=>'#000000'),
        );
    }
}
?>
```

### 2. Core Color Presets Manager
**File**: `include/lib/color_presets_manager.php`

```php
<?php
/**
 * Color Presets Manager
 * Core functionality for managing color presets across the application
 */
class ColorPresetsManager {
    
    private static $instance = null;
    private $presets = null;
    
    /**
     * Get singleton instance
     * @return ColorPresetsManager
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load presets with caching
     * @return array Preset data
     */
    public function getPresets() {
        if ($this->presets === null) {
            $this->presets = $this->loadPresetsFromConfig();
        }
        return $this->presets;
    }
    
    /**
     * Validate color format
     * @param string $color Color value
     * @return bool Is valid
     */
    public function validateColor($color) {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color);
    }
    
    /**
     * Get preset by name
     * @param string $name Preset name
     * @return array|null Preset data or null
     */
    public function getPresetByName($name) {
        $presets = $this->getPresets();
        foreach (['area1', 'area2'] as $area) {
            foreach ($presets[$area] as $preset) {
                if ($preset['name'] === $name) {
                    return $preset;
                }
            }
        }
        return null;
    }
    
    /**
     * Load presets from configuration
     * @return array Preset data
     */
    private function loadPresetsFromConfig() {
        $presets = ['area1' => null, 'area2' => null];
        $json_file = _navigation::GetBasePath() . '/include/config/color_presets.json';
        
        if (file_exists($json_file)) {
            $json_data = @file_get_contents($json_file);
            $data = @json_decode($json_data, true);
            if (is_array($data)) {
                if (isset($data['area1']) && is_array($data['area1'])) {
                    $presets['area1'] = $data['area1'];
                }
                if (isset($data['area2']) && is_array($data['area2'])) {
                    $presets['area2'] = $data['area2'];
                }
            }
        }
        
        return $presets;
    }
}
?>
```

## Responsive Design

### Layout Breakpoints
- **Desktop/Tablet**: ≥768px - Side-by-side layout
- **Mobile**: <768px - Stacked layout

### Desktop/Tablet Layout (≥768px)
```css
/* Desktop/Tablet Layout */
@media (min-width: 768px) {
    .color-settings-container {
        display: flex;
        gap: 20px;
    }
    
    .color-inputs-section {
        flex: 0 0 400px;
    }
    
    .preview-section {
        flex: 1;
        min-height: 400px;
    }
    
    .preset-sections {
        margin-top: 20px;
    }
}
```

### Mobile Layout (<768px)
```css
/* Mobile Layout */
@media (max-width: 767px) {
    .color-settings-container {
        flex-direction: column;
    }
    
    .color-inputs-section,
    .preview-section {
        width: 100%;
    }
    
    .preview-pair {
        display: flex;
        gap: 8px;
        margin-top: 20px;
    }
    
    .preview-pair > div {
        flex: 1 1 50%;
        max-width: 50%;
    }
    
    .mobile-switch-colors {
        text-align: center;
        margin-top: 15px;
    }
}
```

## Performance Optimizations

### 1. Lazy Loading
```javascript
// Load preset data only when sections are expanded
function loadPresetData(areaId) {
    if (!presetDataCache[areaId]) {
        // Load data via AJAX
        $.get('/api/color-presets/' + areaId, function(data) {
            presetDataCache[areaId] = data;
            renderPresets(areaId, data);
        });
    }
}
```

### 2. Debounced Color Updates
```javascript
// Debounce color input changes
const debouncedColorUpdate = debounce(function(colorValue) {
    updatePreview(colorValue);
    saveColorChanges(colorValue);
}, 300);

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
```

### 3. CSS Optimization
```css
/* Use CSS custom properties for better performance */
:root {
    --color-primary: #009901;
    --color-secondary: #000000;
    --color-primary-fg: #ffffff;
    --color-secondary-fg: #ffffff;
}

/* Optimize transitions */
.preset-row {
    transition: box-shadow 0.15s ease, transform 0.15s ease;
    will-change: transform, box-shadow;
}

/* Use transform instead of margin/padding for animations */
.preset-section {
    transform: translateY(0);
    transition: transform 0.3s ease;
}

.preset-section.collapsed {
    transform: translateY(-100%);
}
```

## Accessibility Features

### 1. ARIA Labels and Descriptions
```html
<div class="color-presets-section" 
     role="region" 
     aria-labelledby="color-presets-heading"
     aria-describedby="color-presets-description">
    
    <h3 id="color-presets-heading">Color Presets</h3>
    <p id="color-presets-description">
        Choose from pre-configured color combinations for your branding
    </p>
    
    <div class="preset-area" 
         role="group" 
         aria-labelledby="popular-presets-heading">
        
        <h4 id="popular-presets-heading">Popular Brokerage Presets</h4>
        
        <button class="preset-toggle" 
                aria-expanded="false"
                aria-controls="popular-presets-list">
            <span class="toggle-icon">+</span>
            Popular Brokerage Presets
        </button>
        
        <div id="popular-presets-list" 
             class="preset-list" 
             role="listbox" 
             aria-label="Popular brokerage color presets">
            
            <div class="preset-option" 
                 role="option" 
                 aria-selected="false"
                 tabindex="0"
                 data-preset="black-gold">
                <span class="preset-name">Black & Gold</span>
                <div class="preset-swatches" aria-label="Color swatches">
                    <span class="swatch" style="background: #040404" aria-label="Primary color: dark gray"></span>
                    <span class="swatch" style="background: #edb732" aria-label="Secondary color: gold"></span>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 2. Keyboard Navigation
```javascript
// Enhanced keyboard navigation
function setupKeyboardNavigation() {
    const presetOptions = document.querySelectorAll('.preset-option');
    
    presetOptions.forEach(option => {
        option.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    selectPreset(this);
                    break;
                    
                case 'ArrowDown':
                    e.preventDefault();
                    navigateToNext(option);
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    navigateToPrevious(option);
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    focusFirstOption();
                    break;
                    
                case 'End':
                    e.preventDefault();
                    focusLastOption();
                    break;
            }
        });
    });
}

function navigateToNext(currentOption) {
    const nextOption = currentOption.nextElementSibling;
    if (nextOption && nextOption.classList.contains('preset-option')) {
        nextOption.focus();
    }
}

function navigateToPrevious(currentOption) {
    const prevOption = currentOption.previousElementSibling;
    if (prevOption && prevOption.classList.contains('preset-option')) {
        prevOption.focus();
    }
}
```

### 3. Screen Reader Support
```javascript
// Announce changes to screen readers
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    
    document.body.appendChild(announcement);
    
    // Remove after announcement
    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
}

// Use for color changes
function onColorChange(newColor) {
    updatePreview(newColor);
    announceToScreenReader(`Color changed to ${newColor}`);
}
```

## Integration Points

### 1. Agent Implementation
**File**: `include/classes/c_agent.php`

```php
<?php
require_once(_navigation::GetBasePath() . '/include/traits/t_color_presets.php');

class c_agent extends c_base {
    use t_color_presets;
    
    public function EditSettings() {
        // ... existing code ...
        
        // Color settings section
        echo("<div class='card_label'>Company/Brand Colors</div>");
        echo("<div class='card_section' data-info='SETTINGS_COLORS' data-info-none='none'>");
        
        // Color inputs
        $this->renderColorInputs();
        
        // Mobile preview
        $this->renderMobilePreview();
        
        // Color presets
        $presets = $this->loadColorPresets();
        echo($this->generateColorPresetsHTML('agent', $presets));
        
        // Reset button
        $this->renderResetButton();
        
        echo("</div>");
        
        // Desktop preview
        $this->renderDesktopPreview();
        
        // ... rest of existing code ...
    }
    
    private function renderColorInputs() {
        // ... color input fields ...
    }
    
    private function renderMobilePreview() {
        // ... mobile preview HTML ...
    }
    
    private function renderDesktopPreview() {
        // ... desktop preview HTML ...
    }
    
    private function renderResetButton() {
        // ... reset button HTML ...
    }
}
?>
```

### 2. Coordinator Implementation
**File**: `include/classes/c_coordinator.php`

```php
<?php
require_once(_navigation::GetBasePath() . '/include/traits/t_color_presets.php');

class c_coordinator extends c_base {
    use t_color_presets;
    
    public function EditSettings() {
        // ... existing code ...
        
        // Color settings section
        echo("<div class='card_label'>Company/Brand Colors</div>");
        echo("<div class='card_section' data-info='SETTINGS_COLORS' data-info-none='none'>");
        
        // Color inputs
        $this->renderColorInputs();
        
        // Mobile preview
        $this->renderMobilePreview();
        
        // Color presets
        $presets = $this->loadColorPresets();
        echo($this->generateColorPresetsHTML('coordinator', $presets));
        
        // Reset button
        $this->renderResetButton();
        
        echo("</div>");
        
        // Desktop preview
        $this->renderDesktopPreview();
        
        // ... rest of existing code ...
    }
    
    // ... same private methods as agent ...
}
?>
```

## Admin Implementation

### Overview
The admin implementation will be based on the agent layout and functionality but adapted for administrative use. This section provides the foundation for implementing color presets in the admin area.

### Implementation Location
**Target**: `admin/agents.php?action=edit_agents_new0` - Company/Brand Colors section

### Key Differences from Agent Implementation
1. **User Context**: Admin editing agent colors vs. agent editing own colors
2. **Permissions**: Admin can edit any agent's colors
3. **Audit Trail**: Track who made color changes and when
4. **Bulk Operations**: Apply colors to multiple agents (future enhancement)

### Implementation Steps
1. **Create Admin Color Presets Trait**
2. **Integrate with Admin Agent Editing**
3. **Add Admin-Specific Features**
4. **Implement Audit Logging**

### Admin Color Presets Trait
**File**: `include/traits/t_admin_color_presets.php`

```php
<?php
trait t_admin_color_presets {
    use t_color_presets;
    
    /**
     * Generate admin-specific color presets HTML
     * @param string $prefix Unique prefix for IDs and classes
     * @param array $presets Preset data
     * @param int $agent_id Agent being edited
     * @return string HTML output
     */
    protected function generateAdminColorPresetsHTML($prefix, $presets, $agent_id) {
        $html = '<div class="admin-color-presets" data-agent-id="' . $agent_id . '">';
        $html .= '<div class="admin-presets-header">';
        $html .= '<h4>Color Presets for Agent</h4>';
        $html .= '<p class="admin-presets-description">Select colors for this agent\'s branding</p>';
        $html .= '</div>';
        
        $html .= $this->generateColorPresetsHTML($prefix, $presets);
        
        // Admin-specific features
        $html .= $this->generateAdminFeatures($agent_id);
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate admin-specific features
     * @param int $agent_id Agent being edited
     * @return string HTML output
     */
    private function generateAdminFeatures($agent_id) {
        $html = '<div class="admin-presets-features">';
        
        // Copy colors from other agents
        $html .= '<div class="admin-feature">';
        $html .= '<label for="copy-from-agent">Copy colors from:</label>';
        $html .= '<select id="copy-from-agent" class="form-control">';
        $html .= '<option value="">Select an agent...</option>';
        $html .= $this->getAgentOptions($agent_id);
        $html .= '</select>';
        $html .= '<button type="button" class="btn btn-sm btn-secondary" onclick="copyAgentColors()">Copy</button>';
        $html .= '</div>';
        
        // Reset to default
        $html .= '<div class="admin-feature">';
        $html .= '<button type="button" class="btn btn-sm btn-warning" onclick="resetToDefault(' . $agent_id . ')">Reset to Default</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get agent options for copy dropdown
     * @param int $exclude_id Agent ID to exclude
     * @return string HTML options
     */
    private function getAgentOptions($exclude_id) {
        $html = '';
        $agents = $this->getActiveAgents();
        
        foreach ($agents as $agent) {
            if ($agent['agent_id'] != $exclude_id) {
                $html .= '<option value="' . $agent['agent_id'] . '">' . htmlspecialchars($agent['agent_name']) . '</option>';
            }
        }
        
        return $html;
    }
    
    /**
     * Get active agents
     * @return array Agent data
     */
    private function getActiveAgents() {
        // Implementation to get active agents
        // This would query the database for active agents
        return array();
    }
}
?>
```

### Admin JavaScript Functions
```javascript
// Copy colors from another agent
function copyAgentColors() {
    const agentId = document.getElementById('copy-from-agent').value;
    if (!agentId) {
        alert('Please select an agent to copy colors from');
        return;
    }
    
    // AJAX call to get agent colors
    $.get('/admin/api/agent-colors/' + agentId, function(data) {
        if (data.success) {
            // Apply colors to form
            $('#coordinator_color1_hex').val(data.colors.color1);
            $('#coordinator_color1_fg_hex').val(data.colors.color1fg);
            $('#coordinator_color2_hex').val(data.colors.color2);
            $('#coordinator_color2_fg_hex').val(data.colors.color2fg);
            
            // Update preview
            updatePreview();
            
            // Log the action
            logAdminAction('copy_colors', {
                from_agent: agentId,
                to_agent: currentAgentId
            });
        }
    });
}

// Reset agent colors to default
function resetToDefault(agentId) {
    if (confirm('Are you sure you want to reset this agent\'s colors to default?')) {
        // AJAX call to reset colors
        $.post('/admin/api/reset-agent-colors', {
            agent_id: agentId
        }, function(data) {
            if (data.success) {
                // Reload the form
                location.reload();
                
                // Log the action
                logAdminAction('reset_colors', {
                    agent_id: agentId
                });
            }
        });
    }
}

// Log admin actions
function logAdminAction(action, data) {
    $.post('/admin/api/log-action', {
        action: action,
        data: JSON.stringify(data),
        timestamp: new Date().toISOString()
    });
}
```

## Testing & Validation

### 1. Unit Tests
**File**: `tests/php/ColorPresetsTest.php`

```php
<?php
class ColorPresetsTest extends PHPUnit_Framework_TestCase {
    
    public function testPresetLoading() {
        $manager = ColorPresetsManager::getInstance();
        $presets = $manager->getPresets();
        
        $this->assertArrayHasKey('area1', $presets);
        $this->assertArrayHasKey('area2', $presets);
        $this->assertGreaterThan(0, count($presets['area1']));
        $this->assertGreaterThan(0, count($presets['area2']));
    }
    
    public function testColorValidation() {
        $manager = ColorPresetsManager::getInstance();
        
        $this->assertTrue($manager->validateColor('#000000'));
        $this->assertTrue($manager->validateColor('#FFFFFF'));
        $this->assertFalse($manager->validateColor('000000'));
        $this->assertFalse($manager->validateColor('#000'));
        $this->assertFalse($manager->validateColor('invalid'));
    }
    
    public function testPresetByName() {
        $manager = ColorPresetsManager::getInstance();
        $preset = $manager->getPresetByName('Black & Gold');
        
        $this->assertNotNull($preset);
        $this->assertEquals('Black & Gold', $preset['name']);
        $this->assertEquals('#040404', $preset['c1']);
    }
}
?>
```

### 2. Integration Tests
**File**: `tests/php/ColorPresetsIntegrationTest.php`

```php
<?php
class ColorPresetsIntegrationTest extends PHPUnit_Framework_TestCase {
    
    public function testAgentColorPresets() {
        $agent = new agent(1);
        $settings_html = $agent->EditSettings();
        
        // Check if color presets are included
        $this->assertContains('Popular Brokerage Presets', $settings_html);
        $this->assertContains('Other Color Combinations', $settings_html);
        $this->assertContains('preset-row', $settings_html);
    }
    
    public function testCoordinatorColorPresets() {
        $coordinator = new coordinator(1);
        $settings_html = $coordinator->EditSettings();
        
        // Check if color presets are included
        $this->assertContains('Popular Brokerage Presets', $settings_html);
        $this->assertContains('Other Color Combinations', $settings_html);
        $this->assertContains('preset-row', $settings_html);
    }
}
?>
```

### 3. Browser Testing Checklist
- [ ] Desktop (≥768px) - Side-by-side layout
- [ ] Tablet (768px-1024px) - Same as desktop
- [ ] Mobile (<768px) - Stacked layout
- [ ] Touch interactions work correctly
- [ ] Keyboard navigation functional
- [ ] Screen reader compatibility
- [ ] Color contrast meets accessibility standards
- [ ] Preset selection works across all screen sizes
- [ ] Color switching functionality works
- [ ] Preview updates in real-time

## Implementation Checklist

### Phase 1: Core Infrastructure
- [ ] Create `t_color_presets.php` trait
- [ ] Create `color_presets_manager.php` class
- [ ] Update `color_presets.json` configuration
- [ ] Create dedicated CSS file
- [ ] Create core JavaScript functionality

### Phase 2: Agent Integration
- [ ] Update `c_agent.php` to use trait
- [ ] Test agent color presets functionality
- [ ] Validate responsive design
- [ ] Test accessibility features

### Phase 3: Coordinator Integration
- [ ] Update `c_coordinator.php` to use trait
- [ ] Test coordinator color presets functionality
- [ ] Ensure consistency with agent implementation

### Phase 4: Admin Implementation
- [ ] Create `t_admin_color_presets.php` trait
- [ ] Integrate with admin agent editing
- [ ] Test admin-specific features
- [ ] Implement audit logging

### Phase 5: Testing & Optimization
- [ ] Run comprehensive tests
- [ ] Performance optimization
- [ ] Accessibility validation
- [ ] Cross-browser testing

## Conclusion

This implementation guide provides a comprehensive, standardized approach to implementing color presets across agents, coordinators, and admin users. The modular design ensures code reusability while maintaining consistency across different user types.

Key benefits of this approach:
- **Maintainable**: Single source of truth for color presets logic
- **Scalable**: Easy to add new user types or features
- **Accessible**: WCAG compliant with comprehensive screen reader support
- **Performance**: Optimized with lazy loading and debounced updates
- **Responsive**: Consistent experience across all device types

The admin implementation is structured as a separate phase, allowing you to implement it when ready while maintaining the same core functionality and user experience.
