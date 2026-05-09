# User Profile Edit Feature

## Files Added

- `profile.php` — User profile page with edit form and avatar upload
- `api_profile_update.php` — Backend API to update name and email
- `api_avatar_upload.php` — Backend API to upload and save avatar
- `migrations/002_add_avatar_column.sql` — Adds avatar_path column to employees table
- `avatars/` — Directory for storing user avatar images

## Setup

1. **Run the migration** to add the avatar_path column:
   ```sql
   ALTER TABLE `employees` ADD COLUMN `avatar_path` VARCHAR(255) DEFAULT NULL;
   ```

2. **Create the avatars directory:**
   ```bash
   mkdir -p Features/avatars
   chmod 755 Features/avatars
   ```

3. **Add a link to the profile page** in your navigation (e.g., in the user dropdown menu):
   ```html
   <a href="../Features/profile.php">My Profile</a>
   ```

## Features

- **View Profile** — Display current name, email, and avatar
- **Edit Name & Email** — Update personal information with validation
- **Upload Avatar** — Choose and upload a profile picture (5MB limit)
- **Avatar Storage** — Files saved as `avatar_<user_id>_<timestamp>.<ext>`

## URL to Access

- Profile: `http://localhost/geofence_test/Features/profile.php` (requires login)

## Validation

- Email must be valid email format
- Email cannot be used by another user
- Avatar must be: JPEG, PNG, GIF, or WebP
- Avatar max size: 5MB
- Old avatars are automatically deleted when new one is uploaded

## Security

- Session required (user must be logged in)
- MIME type validation on upload
- File size limits enforced
- Old avatars cleaned up automatically
