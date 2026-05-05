# Premium Tables CRUD Management System

## Overview
This system enables dynamic management of tax, SSS, PhilHealth, and Pag-IBIG contribution tables by year. Instead of hardcoding values in HTML, all table data is now stored in a database and can be easily modified, updated, and deleted through a user-friendly web interface.

## Database Tables

### 1. **tax_table**
Stores withholding tax brackets and rates by year.

**Columns:**
- `id` - Unique identifier
- `year` - Tax year (2020-2026+)
- `income_from` - Lower income bound (DECIMAL 15,2)
- `income_to` - Upper income bound (DECIMAL 15,2, nullable)
- `tax_rate` - Tax rate percentage (DECIMAL 5,2)
- `base_tax` - Base amount for progressive calculation (DECIMAL 15,2)
- `description` - Human-readable range description (VARCHAR 255)
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

**2026 Default Data:** 6 tax brackets (0% to 35%)

---

### 2. **sss_table**
Stores SSS contribution amounts by salary range and year.

**Columns:**
- `id` - Unique identifier
- `year` - Contribution year (2020-2026+)
- `salary_from` - Lower salary bound (DECIMAL 15,2)
- `salary_to` - Upper salary bound (DECIMAL 15,2, nullable)
- `monthly_contribution` - Monthly SSS contribution amount (DECIMAL 10,2)
- `description` - Human-readable range description (VARCHAR 255)
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

**2026 Default Data:** 30 salary brackets with contributions ranging from ₱250 to ₱1,000

---

### 3. **philhealth_table**
Stores PhilHealth contribution rates and maximums by salary range and year.

**Columns:**
- `id` - Unique identifier
- `year` - Contribution year (2020-2026+)
- `salary_from` - Lower salary bound (DECIMAL 15,2)
- `salary_to` - Upper salary bound (DECIMAL 15,2, nullable)
- `contribution_rate` - Contribution rate (0-1 decimal, e.g., 0.0275 for 2.75%) (DECIMAL 5,4)
- `maximum_contribution` - Maximum contribution amount (DECIMAL 10,2)
- `fixed_amount` - Fixed amount for specific ranges (DECIMAL 10,2, nullable)
- `description` - Human-readable range description (VARCHAR 255)
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

**2026 Default Data:** 3 salary ranges with rates and maximums

---

### 4. **pagibig_table**
Stores Pag-IBIG contribution rates and maximums by salary range and year.

**Columns:**
- `id` - Unique identifier
- `year` - Contribution year (2020-2026+)
- `salary_from` - Lower salary bound (DECIMAL 15,2)
- `salary_to` - Upper salary bound (DECIMAL 15,2, nullable)
- `contribution_rate` - Contribution rate (0-1 decimal, e.g., 0.01 for 1%) (DECIMAL 5,4)
- `maximum_contribution` - Maximum contribution amount (DECIMAL 10,2)
- `description` - Human-readable range description (VARCHAR 255)
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

**2026 Default Data:** 3 salary ranges with rates and maximums

---

## API Endpoints

All APIs follow RESTful conventions and return JSON responses.

### Tax Table API: `/tax_table_api.php`

**GET - Retrieve tax data for a year**
```
GET /tax_table_api.php?year=2026
Response: { "success": true, "data": [...], "message": "Tax table data retrieved" }
```

**POST - Create a new tax bracket**
```
POST /tax_table_api.php
Content-Type: application/json
{
  "year": 2027,
  "income_from": 20000,
  "income_to": 35000,
  "tax_rate": 15.00,
  "base_tax": 0,
  "description": "₱20,000 - ₱35,000"
}
```

**PUT - Update an existing tax bracket**
```
PUT /tax_table_api.php
Content-Type: application/json
{
  "id": 1,
  "tax_rate": 16.00,
  "base_tax": 50
}
```

**DELETE - Remove a tax bracket**
```
DELETE /tax_table_api.php?id=1
```

---

### SSS Table API: `/sss_table_api.php`

**GET - Retrieve SSS data for a year**
```
GET /sss_table_api.php?year=2026
```

**POST - Create a new SSS record**
```
POST /sss_table_api.php
Content-Type: application/json
{
  "year": 2027,
  "salary_from": 5250,
  "salary_to": 5750,
  "monthly_contribution": 275,
  "description": "₱5,250 - ₱5,749"
}
```

**PUT - Update SSS record**
```
PUT /sss_table_api.php
Content-Type: application/json
{
  "id": 1,
  "monthly_contribution": 280
}
```

**DELETE - Remove SSS record**
```
DELETE /sss_table_api.php?id=1
```

---

### PhilHealth Table API: `/philhealth_table_api.php`

**GET, POST, PUT, DELETE** - Same structure as SSS API

Additional fields for PhilHealth:
- `contribution_rate` (DECIMAL 5,4) - Rate as decimal (e.g., 0.0275)
- `fixed_amount` - Optional fixed amount instead of percentage
- `maximum_contribution` - Maximum amount to contribute

---

### Pag-IBIG Table API: `/pagibig_table_api.php`

**GET, POST, PUT, DELETE** - Same structure as SSS API

---

## Frontend Usage

### Loading Data
Each table loads data automatically when selected using the `toggleTableData()` function:
```javascript
// Called when year dropdown changes
toggleTableData('taxTable', 'taxYearSelect')  // Loads tax data for selected year
toggleTableData('sssTable', 'sssYearSelect')  // Loads SSS data for selected year
toggleTableData('philhealthTable', 'philhealthYearSelect')  // etc.
toggleTableData('pagibigTable', 'pagibigYearSelect')
```

### Creating New Records
Click the **"+ Add Record"** button in the table header to open the management form:
- Fill in all required fields
- Optional fields can be left blank
- Click **Save** to submit or **Cancel** to close the form
- Table automatically refreshes after save

### Editing Records
1. Click the **Edit** button in the Actions column
2. Form pre-populates with current values
3. Modify any fields
4. Click **Save** to update
5. Table automatically refreshes

### Deleting Records
1. Click the **Delete** button in the Actions column
2. Confirm deletion in the popup
3. Record is removed from database
4. Table automatically refreshes

---

## Field Specifications

### Tax Table Fields
| Field | Type | Required | Notes |
|-------|------|----------|-------|
| Year | Number | ✓ | 2020-2100+ |
| Income From | Decimal(15,2) | ✓ | In PHP currency |
| Income To | Decimal(15,2) | - | Leave blank for "above" ranges |
| Tax Rate (%) | Decimal(5,2) | ✓ | 0-100 |
| Base Amount | Decimal(15,2) | - | For progressive calculation |
| Description | Text | - | e.g., "₱20,000 - ₱35,000" |

### SSS/Pag-IBIG Fields
| Field | Type | Required | Notes |
|-------|------|----------|-------|
| Year | Number | ✓ | 2020-2100+ |
| Salary From | Decimal(15,2) | ✓ | In PHP currency |
| Salary To | Decimal(15,2) | - | Leave blank for "above" ranges |
| Contribution/Rate | Decimal(10,2 or 5,4) | ✓ | Amount or rate (e.g., 0.01 = 1%) |
| Maximum | Decimal(10,2) | ✓ | Max contribution cap |
| Description | Text | - | e.g., "₱5,250 - ₱5,749" |

### PhilHealth Additional Fields
| Field | Type | Required | Notes |
|-------|------|----------|-------|
| Fixed Amount | Decimal(10,2) | - | Use instead of rate for fixed contributions |

---

## Managing Data for New Years

### Adding a New Year (e.g., 2027)

1. Go to **Payroll → Premiums → [Table Name]**
2. Change Year dropdown to select 2027 (you can manually type if not listed)
3. Click **"+ Add Record"**
4. Enter salary/income ranges and corresponding contribution rates
5. Fill in Description for clarity
6. Click Save

**Tips for 2027 Migration:**
- Copy all 2026 records first
- Update only the values that changed
- Verify contribution rates with government agencies
- Test calculations with sample employee salaries

### Updating Existing Year Data

1. Go to the table section
2. Select the year from dropdown
3. Click **Edit** on the record to modify
4. Update the relevant fields
5. Click **Save**

---

## JavaScript Classes

The frontend uses four manager classes (defined in `premium_tables_manager.js`):

- **TaxTableManager** - Handles tax table CRUD
- **SssTableManager** - Handles SSS table CRUD
- **PhilhealthTableManager** - Handles PhilHealth table CRUD
- **PagibigTableManager** - Handles Pag-IBIG table CRUD

Each manager instance:
- Fetches data from its corresponding API
- Renders dynamic table rows
- Manages form state (add/edit)
- Deletes records with confirmation
- Auto-refreshes after changes

---

## Database Setup

### Option 1: Import SQL Schema
Run the schema file to create all tables:
```sql
SOURCE premium_tables_schema.sql;
```

### Option 2: Manual Table Creation
Copy and paste the CREATE TABLE statements from `premium_tables_schema.sql` into your database admin tool.

### Option 3: Auto-Creation
The API files include `ensureTableExists()` functions that automatically create tables on first use if they don't exist.

---

## Error Handling

All API endpoints return JSON with this structure:
```json
{
  "success": true/false,
  "data": null or array/object,
  "message": "Human-readable message"
}
```

The frontend displays errors in browser alerts with detailed messages from the server. Check the browser console (F12) for additional details.

---

## Performance Considerations

- Tables index on `year` column for fast filtering
- Minimal queries per request (no N+1 problems)
- Frontend pagination not needed (data sets typically < 50 records)
- Year selection limits data to single year at a time

---

## Future Enhancements

Potential improvements:
1. Export/Import year data (Excel/CSV)
2. Copy entire year (clone from 2026 to 2027)
3. Batch edit multiple records
4. Version history tracking
5. Audit logs (who changed what when)
6. Calculate net pay using selected year's tables

---

## Troubleshooting

**Issue:** Tables show "No data available for year XXXX"
- **Solution:** Make sure the year has been added to the database. Click "+ Add Record" to create entries.

**Issue:** Changes don't appear after save
- **Solution:** Refresh the page or change year dropdown and change it back to trigger reload.

**Issue:** Forms not showing after clicking "+ Add Record"
- **Solution:** Check browser console (F12) for JavaScript errors. Ensure `premium_tables_manager.js` is loaded.

**Issue:** "Failed to insert record" error
- **Solution:** Ensure all required fields are filled. Check that values are in correct format (numbers, not text).

---

## Files Modified/Created

**New Files:**
- `premium_tables_schema.sql` - Database schema
- `premium_tables_manager.js` - Frontend CRUD logic
- `tax_table_api.php` - API for tax table CRUD
- `sss_table_api.php` - API for SSS table CRUD
- `philhealth_table_api.php` - API for PhilHealth table CRUD
- `pagibig_table_api.php` - API for Pag-IBIG table CRUD

**Modified Files:**
- `index.html` - Updated premium table sections with dynamic forms

---

## Support

For issues or questions:
1. Check this documentation first
2. Review browser console (F12) for errors
3. Check PHP error logs if API calls fail
4. Verify database connectivity in `../../Modules/dbcon.php`
