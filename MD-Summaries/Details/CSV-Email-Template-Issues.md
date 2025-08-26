# CSV and Email Template Issues - August 26, 2025

## **Overview**
This document details the investigation and partial resolution of issues with the archive transaction functionality, specifically CSV file attachments and email template image display.

## **Issues Identified**

### **1. CSV File Attachment Failure**
**Problem:** Archive emails were being sent without CSV file attachments, despite the archive process completing successfully.

**Root Cause:** Temp directory permissions were preventing file creation in `/dynamic/temp/` directory.

**Symptoms:**
- Archive process appeared to complete successfully
- Emails were sent to agent and coordinators
- No error messages in logs
- CSV files were not created or attached

**Investigation Steps:**
1. Created debug scripts to test file creation
2. Identified temp directory was not writable by web server
3. Confirmed directory existed but lacked proper permissions

**Resolution:** Fixed temp directory permissions allowing CSV file creation and attachment.

### **2. Email Template Image Display Failure**
**Problem:** Property images were not displaying in archive emails, even when the user had image files set.

**Root Cause:** Custom template syntax `<if user_image_file/>` was not being processed by the email system.

**Symptoms:**
- Images displayed correctly in web interface
- Archive emails sent successfully
- Image variables properly populated
- Template rendering failed to show images

**Investigation Steps:**
1. Confirmed user 242 had image file: `2-web-or-mls-7961CalleMadrid02242_r.jpg`
2. Verified image file exists on disk (328,027 bytes)
3. Confirmed image URL generation working correctly
4. Identified custom template syntax not being processed

**Resolution Attempted:** Replaced custom `<if user_image_file/>` syntax with standard HTML and onerror fallback.

## **Technical Details**

### **CSV Creation Process**
```php
// File creation in ArchiveTransaction() method
$filename = mod_rewrite::ToURL($this->Get('user_name')).'-Activity-Log'.'.csv';
$temp_file_path = file::GetPath('temp').$filename;
$f = fopen($temp_file_path, 'w');

// File attachment to email
if($f !== null && file_exists($temp_file_path)) {
    $files[$filename] = $temp_file_path;
}
```

### **Email Template Variables**
```php
// Template variables set for email
$mail_params['user_image_file'] = $user->Get('user_image_file') ? 
    _navigation::GetBaseURL() . "dynamic/images/users/" . $user->Get('user_image_file') : '';
$mail_params['agent_image_file1'] = $agent->Get('agent_image_file1') ? 
    _navigation::GetBaseURL() . "dynamic/images/agents/" . $agent->Get('agent_image_file1') : '';
```

### **Template Syntax Changes**
**Before (Not Working):**
```html
<if user_image_file/>
    <div style='text-align:center'><img src='<user_image_file/>'></div>
    <br>
</if user_image_file/>
```

**After (Attempted Fix):**
```html
<div style='text-align:center'>
    <img src='<user_image_file/>' style='max-width:100%;max-height:300px;' onerror="this.style.display='none';">
</div>
```

## **Current Status**

### **✅ Resolved Issues**
- **CSV Attachment:** Working correctly after temp directory permission fix
- **Email Sending:** Archive emails sent successfully to agent + coordinators
- **Opt-out Links:** Functional with proper URL generation
- **Archive Process:** Completing successfully with proper data collection

### **❌ Unresolved Issues**
- **Image Display:** Still not showing in archive emails despite template fix
- **Template Processing:** Further investigation needed for image rendering

## **Testing Results**

### **CSV Functionality Test**
- **User ID:** 242 (Demo Seller Nov - 123 ABC Street. Carlsbad CA 92009)
- **Result:** CSV file created successfully (328,027 bytes)
- **Email:** Sent with CSV attachment
- **Status:** ✅ Working

### **Image Display Test**
- **User ID:** 242 (has image: `2-web-or-mls-7961CalleMadrid02242_r.jpg`)
- **Template Processing:** Variables replaced correctly
- **Image URL:** Generated correctly
- **Final Result:** Image still not displaying in email
- **Status:** ❌ Not Working

## **Next Steps Required**

### **Immediate Actions**
1. **Further debugging** of email template processing
2. **Investigation** of email rendering system
3. **Testing** with different email clients
4. **Verification** of template variable replacement

### **Potential Solutions to Investigate**
1. **Email client compatibility** - Test with different email clients
2. **Template caching** - Check if old templates are cached
3. **Email system configuration** - Verify email template processing
4. **Alternative image display methods** - Consider different approaches

## **Files Modified**

### **include/classes/c_user.php**
- Enhanced ArchiveTransaction() method with proper error handling
- Added debug logging for CSV creation process
- Improved file attachment logic

### **email_templates/email_activity_log.html**
- Replaced custom template syntax with standard HTML
- Added graceful fallback for image display
- Improved email template structure

## **Debug Scripts Created**

### **debug_user_242.php**
- Tests user 242's image file availability
- Verifies file existence and permissions
- Confirms email template variable population

### **test_email_template.php**
- Tests email template processing
- Verifies variable replacement
- Shows processed template output

## **Lessons Learned**

1. **Temp directory permissions** are critical for file creation functionality
2. **Custom template syntax** may not be compatible with email systems
3. **Debug logging** is essential for troubleshooting file and email issues
4. **Template processing** requires understanding of the email system's capabilities

## **Related Documentation**

- [Archive CSV Header Enhancement](ARCHIVE-CSV-HEADER.md)
- [Print Timeline Header Enhancement](Print%20Timeline-add%20header.md)
- [Coordinator Proxy Banner Exit Link](Coordinator-Proxy-Banner-Exit-Link.md)

---

**Document Status:** Active Investigation  
**Last Updated:** August 26, 2025  
**Next Review:** After image display issue resolution
