# QA Test Plan & Checklist

This document provides a comprehensive testing guide for the three new features:
1. Assign Employee to Geofence
2. Forgot Password / Password Reset
3. User Profile Edit

---

## Feature 1: Assign Employee to Geofence

### Prerequisite Setup
- [ ] HR is logged in
- [ ] At least 1 geofence site exists in database
- [ ] At least 1 employee exists in database

### Test Cases

**Test 1.1: Access Assignment Page**
- Start: Log in as HR user
- Action: Open `http://localhost/geofence_test/HR_features/assign_employee.php`
- Expected: Page loads with two dropdowns and an Assign button
- Status: ☐ PASS ☐ FAIL

**Test 1.2: Form Validation - Empty Selection**
- Start: On assignment page with empty dropdowns
- Action: Click Assign button without selecting site or employee
- Expected: Warning message appears: "Select both site and employee."
- Status: ☐ PASS ☐ FAIL

**Test 1.3: Successful Assignment**
- Start: On assignment page
- Action: Select a geofence and employee, click Assign
- Expected: Success message appears, can verify in DB: `SELECT * FROM employee_location WHERE User_Id=X AND loc_id=Y`
- Status: ☐ PASS ☐ FAIL

**Test 1.4: Duplicate Assignment Prevention**
- Start: Employee already assigned to site (from Test 1.3)
- Action: Try to assign the same employee to the same site again
- Expected: Error message: "Employee already assigned to this site"
- Status: ☐ PASS ☐ FAIL

**Test 1.5: Integration Test**
- Start: On assignment page
- Action: Open `http://localhost/geofence_test/HR_features/test_integration.php`
- Expected: All tests show PASS, employee_location table exists, geofences and employees exist
- Status: ☐ PASS ☐ FAIL

---

## Feature 2: Forgot Password / Password Reset

### Prerequisite Setup
- [ ] At least 1 employee account exists (use "Alexis" / "123" if available)
- [ ] Email server is configured OR you're using debug mode

### Test Cases

**Test 2.1: Access Forgot Password Page**
- Start: Not logged in
- Action: Open `http://localhost/geofence_test/Features/forgot_password.php`
- Expected: Form appears with email/username input field
- Status: ☐ PASS ☐ FAIL

**Test 2.2: Form Validation - Empty Email**
- Start: On forgot password page
- Action: Click "Send Reset Link" without entering email
- Expected: Field shows "required" validation
- Status: ☐ PASS ☐ FAIL

**Test 2.3: Invalid Email/Username**
- Start: On forgot password page
- Action: Enter non-existent email (e.g., "nouser@test.com"), click Send
- Expected: Error message: "Email/username not found"
- Status: ☐ PASS ☐ FAIL

**Test 2.4: Valid Email Request**
- Start: On forgot password page
- Action: Enter valid email or username (e.g., "Alexis" or "axiserondc@gmail.com"), click Send
- Expected: Success message and debug reset link appears (in local mode)
- Database: Check `password_reset_tokens` table - new token should exist
- Status: ☐ PASS ☐ FAIL

**Test 2.5: Reset with Valid Token**
- Start: Have a valid reset token from Test 2.4
- Action: Click the reset link or go to `reset_password.php?token=<TOKEN>`
- Expected: Reset password form appears with two password fields
- Status: ☐ PASS ☐ FAIL

**Test 2.6: Reset Form Validation - Password Mismatch**
- Start: On reset password form
- Action: Enter "newpass123" in first field, "different" in second, click Reset
- Expected: Error message: "Passwords do not match"
- Status: ☐ PASS ☐ FAIL

**Test 2.7: Reset Form Validation - Short Password**
- Start: On reset password form
- Action: Enter "123" in both fields, click Reset
- Expected: Error message: "Password must be at least 6 characters"
- Status: ☐ PASS ☐ FAIL

**Test 2.8: Successful Password Reset**
- Start: On reset password form with valid token
- Action: Enter "newpassword123" in both fields, click Reset
- Expected: Success message, redirected to login page after 2 seconds
- Login: Try to log in with new password - should succeed
- Database: Verify token is deleted from `password_reset_tokens` table
- Status: ☐ PASS ☐ FAIL

**Test 2.9: Expired Token**
- Start: Manually set a token expiration to past time in DB
- Action: Visit reset link with expired token
- Expected: Error message: "Invalid or expired reset token"
- Status: ☐ PASS ☐ FAIL

---

## Feature 3: User Profile Edit

### Prerequisite Setup
- [ ] User is logged in (e.g., "Alexis" user)

### Test Cases

**Test 3.1: Access Profile Page**
- Start: Logged in as a user
- Action: Open `http://localhost/geofence_test/Features/profile.php`
- Expected: Profile page loads with user info, avatar, and edit form
- Status: ☐ PASS ☐ FAIL

**Test 3.2: Redirect When Not Logged In**
- Start: Not logged in (or session expired)
- Action: Try to access `http://localhost/geofence_test/Features/profile.php`
- Expected: Redirected to login page
- Status: ☐ PASS ☐ FAIL

**Test 3.3: Edit Name - Valid Update**
- Start: On profile page logged in
- Action: Change name to "Test User New Name", click Save Changes
- Expected: Success message, name updates on page
- Verify: Check database `SELECT name FROM employees WHERE id=<USER_ID>`
- Status: ☐ PASS ☐ FAIL

**Test 3.4: Edit Email - Valid Update**
- Start: On profile page
- Action: Change email to "newemail@test.com", click Save Changes
- Expected: Success message, email updates on page
- Database: Verify in employees table
- Status: ☐ PASS ☐ FAIL

**Test 3.5: Edit Email - Invalid Format**
- Start: On profile page
- Action: Change email to "invalidemail", click Save Changes
- Expected: Error message: "Invalid email format" (validation happens on client first)
- Status: ☐ PASS ☐ FAIL

**Test 3.6: Edit Email - Duplicate**
- Start: On profile page for user with email "alexis@test.com"
- Action: Try to change email to another user's email that already exists
- Expected: Error message: "Email already in use"
- Status: ☐ PASS ☐ FAIL

**Test 3.7: Avatar Upload - Valid Image**
- Start: On profile page
- Action: Click avatar (camera icon), select a valid JPEG/PNG image, upload
- Expected: Avatar updates immediately, success message appears
- File: Check that file was saved to `Features/avatars/` directory
- Database: Verify avatar_path was saved
- Status: ☐ PASS ☐ FAIL

**Test 3.8: Avatar Upload - File Too Large**
- Start: On profile page
- Action: Click avatar, select image >5MB, upload
- Expected: Error message: "File too large (max 5MB)"
- Status: ☐ PASS ☐ FAIL

**Test 3.9: Avatar Upload - Invalid File Type**
- Start: On profile page
- Action: Click avatar, select a .txt or .pdf file, upload
- Expected: Error message: "Please select an image file"
- Status: ☐ PASS ☐ FAIL

**Test 3.10: Avatar Upload - Replace Old Avatar**
- Start: User already has an avatar
- Action: Upload a different image as new avatar
- Expected: New avatar displays, old file is deleted from server
- File: Verify only new avatar file exists in `Features/avatars/`
- Database: avatar_path points to new file
- Status: ☐ PASS ☐ FAIL

**Test 3.11: Username Cannot Be Changed**
- Start: On profile page
- Action: Attempt to change the username field (disabled/non-editable)
- Expected: Field is disabled or cannot be edited
- Status: ☐ PASS ☐ FAIL

---

## URL Summary

### Feature 1 - Assign Employee
- Main: `http://localhost/geofence_test/HR_features/assign_employee.php`
- Test: `http://localhost/geofence_test/HR_features/test_integration.php`
- API: `http://localhost/geofence_test/HR_features/api_assign_employee.php`

### Feature 2 - Password Reset
- Request: `http://localhost/geofence_test/Features/forgot_password.php`
- Reset: `http://localhost/geofence_test/Features/reset_password.php?token=<TOKEN>`
- API-Request: `http://localhost/geofence_test/Features/api_forgot_password.php`
- API-Reset: `http://localhost/geofence_test/Features/api_reset_password.php`

### Feature 3 - Profile Edit
- Profile: `http://localhost/geofence_test/Features/profile.php`
- API-Update: `http://localhost/geofence_test/Features/api_profile_update.php`
- API-Avatar: `http://localhost/geofence_test/Features/api_avatar_upload.php`

---

## Database Checks

After running all tests, verify database integrity:

```sql
-- Password reset tokens (should be empty after test 2.8)
SELECT * FROM password_reset_tokens;

-- Employee assignments (should have new entries from Test 1.3)
SELECT * FROM employee_location;

-- Employee profile changes (verify from Test 3.3-3.4)
SELECT id, name, email, avatar_path FROM employees WHERE id = <TEST_USER_ID>;

-- Avatar files should exist in Features/avatars/ directory
```

---

## Notes

- Tests marked ☐ should be filled in during manual testing
- Keep this document updated with actual test results
- Report any failures with: test number, steps to reproduce, expected vs actual result
- Integration with existing app (login page links, HR dashboard links) requires manual setup per requirements

