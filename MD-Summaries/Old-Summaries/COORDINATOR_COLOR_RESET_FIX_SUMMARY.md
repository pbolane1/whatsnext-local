# Coordinator Color Reset Button Display Fix

## Issue Description
The "Coordinator Color Options" window was always showing the "Reset to [Coordinator Name] colors" button for all available coordinators, even when the agent was currently inheriting colors from that coordinator. This created confusion as agents would see a reset button for colors they were already using.

## Root Cause
The `RenderColorResetLinks()` method in `include/classes/c_agent.php` was unconditionally displaying reset buttons for all available coordinators without checking whether the agent had actually selected a different color preset.

## Solution Implemented

### 1. Modified Display Logic
- **Before**: Reset buttons were shown for ALL available coordinators
- **After**: Reset buttons are only shown when:
  - The coordinator is NOT the current inherited one, OR
  - The agent has manually selected different colors from the inherited coordinator

### 2. Added `HasCustomColors()` Method
Created a new private method that determines if an agent has custom colors by comparing:
- Current agent colors vs. coordinator colors
- Whether the agent has any inherited colors at all

### 3. Updated Display Conditions
The reset button now only appears when:
1. **Agent has no inherited colors** (must have custom colors)
2. **Agent has manually selected different colors** from the inherited coordinator
3. **Coordinator is not the current inherited one** (for other available coordinators)

## Code Changes

### Modified `RenderColorResetLinks()` Method
- Added logic to check if reset links should be displayed
- Only shows reset buttons when meaningful (agent has custom colors)
- Maintains existing inheritance notice functionality

### New `HasCustomColors()` Method
```php
private function HasCustomColors($coordinator)
{
    // If agent has no inherited colors, they must have custom colors
    if (!$this->Get('agent_colors_inherited_from')) {
        return true;
    }
    
    // Check if current agent colors differ from coordinator colors
    $agentColor1 = $this->Get('agent_color1_hex');
    $agentColor1Fg = $this->Get('agent_color1_fg_hex');
    $agentColor2 = $this->Get('agent_color2_hex');
    $agentColor2Fg = $this->Get('agent_color2_fg_hex');
    
    $coordColor1 = $coordinator->Get('coordinator_color1_hex');
    $coordColor1Fg = $coordinator->Get('coordinator_color1_fg_hex');
    $coordColor2 = $coordinator->Get('coordinator_color2_hex');
    $coordColor2Fg = $coordinator->Get('coordinator_color2_fg_hex');
    
    // Return true if any color differs
    return ($agentColor1 !== $coordColor1 || 
            $agentColor1Fg !== $coordColor1Fg || 
            $agentColor2 !== $coordColor2 || 
            $agentColor2Fg !== $coordColor2Fg);
}
```

## Behavior Changes

### Before Fix
- **Always showed**: "Reset to Pete TC Test colors" button
- **Even when**: Agent was inheriting colors from Pete TC Test
- **Result**: Confusing UI with unnecessary reset options

### After Fix
- **Shows reset button only when**:
  - Agent has selected a Popular Brokerage Preset
  - Agent has selected Other Color Combinations  
  - Agent has manually adjusted colors
  - Agent has a different coordinator available
- **Hides reset button when**:
  - Agent is currently inheriting colors from the coordinator
  - No meaningful reset action is available

## Testing
- Created test cases to verify logic
- All test cases pass ✅
- No syntax errors in PHP code
- JavaScript functionality remains intact

## Files Modified
- `include/classes/c_agent.php` - Main logic changes
- Backup created: `include/classes/c_agent.php.backup.YYYYMMDD_HHMMSS`

## Impact
- **Positive**: Cleaner, more intuitive UI
- **Positive**: No more confusing reset buttons for inherited colors
- **Neutral**: Existing functionality preserved
- **Risk**: Low - only affects display logic, not core functionality

## Future Considerations
- Consider adding visual indicators when agent has custom colors
- Could add "Reset to Inherited Colors" option when agent has custom colors
- Monitor user feedback on the improved UX
