# GoCloud Dashboard Updates Summary

## Features Implemented

### 1. **Inline File Renaming (Edit & Save)**
- File name now displays with an inline edit button instead of modal popup
- Edit button toggles a hidden form with input field
- Save and Cancel buttons for inline operations
- Smooth transitions and auto-focus on edit mode

### 2. **Folder Upload Support**
- Added "Choose Folder" button in upload modal
- Uses `webkitdirectory` HTML5 attribute for folder selection
- Supports uploading entire folder structures at once
- Shows selected file count from both file and folder uploads
- Backend handles both `files[]` and `folder_files[]` input groups

### 3. **File Size Display in Sidebar Folders**
- Folders now show:
  - Total size in KB/MB
  - Number of files in the folder
- Example: "123.5 KB • 5 files"
- Automatically aggregated using `getFolderSizes()` method

### 4. **View Mode Toggle (Grid/List)**
- Grid view button toggles to list view display
- List view shows files in a compact row format
- View mode preference saved to localStorage
- Persists across page reloads

### 5. **Shared Files Section**
- New "Shared Files" page accessible from sidebar
- Shows all files the user has shared with others
- Displays share date and provides link to shared file
- Separate navigation from regular file management

### 6. **Invite Collaborators Section**
- New "Invite Collaborators" button in sidebar (under Shared section)
- Modal form with:
  - Email address input field
  - Permission level dropdown (View Only, Can Edit, Can Share)
  - Email validation on form submission
- Displays success/error message after invite submission

---

## Backend Changes

### FileManager.php
- **`uploadFile()`** - Now normalizes file paths from nested folders
- **`renameFile()`** - Parameter order fixed: `($id, $userId, $newName)`
- **`getFolderSizes()`** - New method aggregates file sizes and counts by folder

### ShareManager.php
- **`getUserShares()`** - New method retrieves all shares for a given user

### Dashboard.php (public/dashboard.php)
- Supports multiple file input groups for folder uploads
- Invite form submission with validation
- Page context variable (`$page`) to differentiate views (shared/regular)
- Redirect handling for folder navigation after uploads/renames
- Status alerts for invites and share actions

### Sidebar.php (components/sidebar.php)
- Displays folder sizes and file counts
- "Shared" menu section added
- "Invite Collaborators" button linked to invite modal
- Page variable support for shared view highlighting

---

## Frontend Changes

### main.js (assets/js/main.js)
- **`toggleRenameForm()`** - New function to toggle inline rename forms
- **`setViewMode()`** - View mode toggle (grid/list) with localStorage persistence
- **File selection summary** - Shows count of selected files/folders in upload modal

### style.css (assets/css/style.css)
- `.inline-rename-form` - Styling for inline edit forms
- `.drop-area.dragover` - Drag-over state styling
- `.folder-size` - Small text styling for folder metadata
- `.drop-area` - Enhanced drop zone with better visual feedback

---

## Database Considerations

The application now uses:
- **folder_id** field in files table for organizing files into folders
- **Existing shares table** for file sharing
- Storage summary from users table (storage_limit, storage_used)

To fully enable invite functionality, consider creating an `invitations` or `collaborators` table:
```sql
CREATE TABLE IF NOT EXISTS invitations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inviter_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    permission ENUM('view', 'edit', 'share') DEFAULT 'view',
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inviter_id) REFERENCES users(id)
);
```

---

## Testing Checklist

- [ ] Upload individual files to dashboard
- [ ] Upload entire folder with nested files
- [ ] Rename file inline (without modal)
- [ ] Toggle between grid and list views
- [ ] View folder sizes in sidebar
- [ ] Navigate to "Shared Files" page
- [ ] Send collaborator invite with email validation
- [ ] Verify localStorage persists grid/list preference
- [ ] Check share link generation and access

---

## Browser Compatibility

- **Folder upload**: Requires webkitdirectory support (Chrome, Edge, Firefox, Safari)
- **Grid/List toggle**: All modern browsers
- **Inline editing**: All modern browsers
- **localStorage**: All modern browsers
