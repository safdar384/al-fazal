# Popup Message System - Usage Guide

## Overview
A reusable popup message system using SweetAlert2 has been created in `public/JS/global.js`. These functions are available on **all pages** throughout the application.

## Available Functions

### 1. Success Messages
```javascript
showSuccess('Record saved successfully!');
showSuccess('Transaction completed!', 'Your balance has been updated');
```

### 2. Error Messages
```javascript
showError('Failed to save record');
showError('Database error', 'Please contact administrator');
```

### 3. Warning Messages
```javascript
showWarning('Please select an agent');
showWarning('Invalid input', 'Amount must be greater than zero');
```

### 4. Info Messages
```javascript
showInfo('Processing your request');
showInfo('Loading data', 'Please wait...');
```

### 5. Custom Popup (Advanced)
```javascript
showPopup('success', 'Title', 'Optional text', 3000); // 3 seconds timer
showPopup('error', 'Error Title', 'Error details', 5000); // 5 seconds
```

## Function Parameters

### showSuccess(title, text, timer)
- **title** (required): Main message text
- **text** (optional): Additional detail text
- **timer** (optional): Auto-close time in milliseconds (default: 2000ms)

### showError(title, text, timer)
- **title** (required): Error message
- **text** (optional): Error details
- **timer** (optional): Auto-close time in milliseconds (default: 3000ms)

### showWarning(title, text, timer)
- **title** (required): Warning message
- **text** (optional): Warning details
- **timer** (optional): Auto-close time in milliseconds (default: 2500ms)

### showInfo(title, text, timer)
- **title** (required): Info message
- **text** (optional): Info details
- **timer** (optional): Auto-close time in milliseconds (default: 2000ms)

## Features
✅ Toast-style notifications (top-right corner)
✅ Auto-close with progress bar
✅ Close button included
✅ Color-coded icons (success=green, error=red, warning=yellow, info=blue)
✅ Works on all pages (loaded in global.js)
✅ No need to import anything

## Where It's Already Implemented

### Agent Account Transaction Page
All alerts have been replaced with popup messages:
- ✅ `showSuccess()` - Transaction saved/updated/deleted
- ✅ `showError()` - Save/update/delete failures
- ✅ `showWarning()` - Validation errors (missing fields, invalid amounts)

## How to Use in Any Page

Simply call the function in your JavaScript code:

```javascript
// In jQuery AJAX
$.ajax({
  url: '/your-endpoint',
  method: 'POST',
  data: formData,
  success: function(response) {
    if(response.success) {
      showSuccess('Data saved successfully!');
    } else {
      showError('Failed to save', response.message);
    }
  },
  error: function(xhr) {
    showError('Server error', xhr.responseJSON?.message || 'Unknown error');
  }
});

// In button click
$('#btnDelete').on('click', function() {
  if(!confirm('Are you sure?')) return; // Still use confirm() for confirmations
  
  // Delete action
  $.ajax({
    url: '/delete',
    method: 'DELETE',
    success: function(res) {
      showSuccess('Deleted successfully!');
    }
  });
});

// Form validation
if(!$('#amount').val()) {
  showWarning('Please enter amount');
  return;
}
```

## Delete Confirmation Dialog

For delete confirmations, use the existing `confirmDelete()` function:

```javascript
// In your delete button
<form id="deleteForm{{ $id }}" action="/delete/{{ $id }}" method="POST">
  @csrf
  @method('DELETE')
</form>

<button onclick="confirmDelete('deleteForm{{ $id }}')">Delete</button>
```

Or use SweetAlert confirm for AJAX deletes:

```javascript
Swal.fire({
  title: 'Are you sure?',
  text: 'This action cannot be undone',
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#dc3545',
  cancelButtonColor: '#6c757d',
  confirmButtonText: 'Yes, delete it!',
  cancelButtonText: 'Cancel'
}).then((result) => {
  if (result.isConfirmed) {
    // Perform delete
    showSuccess('Deleted successfully!');
  }
});
```

## Styling & Position

Current settings (can be customized in global.js):
- Position: `top-end` (top-right corner)
- Type: `toast` (non-blocking notification)
- Timer Progress Bar: Enabled
- Show Close Button: Yes

## Examples from Agent Transaction Page

```javascript
// Success example
showSuccess('Transaction saved successfully!');

// Error with details
showError('Failed to save', res.message || '');

// Warning
showWarning('Please select an agent');

// Error from server
showError('Failed to delete', xhr.responseJSON?.message || '');
```

## Notes
- The popup automatically closes after the timer expires
- Users can manually close by clicking the X button
- Multiple popups can stack vertically
- Popup functions are loaded globally via `global.js`
- No additional imports or setup needed on individual pages
