# Recovery Bank Distribution - Feature Documentation

## Overview
This new feature allows you to manage recovery payments collected from different customers across various areas, and then distribute these accumulated amounts to different bank accounts.

## Key Logic

### Problem Solved
- **Old Way**: Every recovery payment from a customer would directly go to a bank account
- **New Way**: Recovery payments accumulate in a central pool, and you can then distribute them to different banks as needed

### How It Works

#### 1. **Recovery Collection** (Tab 1)
- Shows all recovery amounts collected from different customers
- Each customer recovery can be filtered by Area or Date
- Only shows entries where recovery amount > 0
- Displays:
  - Date of recovery
  - Area name
  - Customer code
  - Description
  - Recovery amount
  - Status (Collected/Pending)

#### 2. **Bank Distribution** (Tab 2)
- Shows all distributions made from the recovery pool to various banks
- Can filter by Bank Account or Distribution Date
- Displays:
  - Distribution date
  - Bank account code
  - Bank name
  - Distribution amount
  - Reference/note for tracking

#### 3. **Distribution Form**
- Takes an amount from the recovery pool
- Routes it to a selected bank account
- Optional reference field for tracking purposes
- **Validation**: Amount cannot exceed "Pending Distribution" (Total Recovery - Already Distributed)

### Dashboard Cards
- **Total Recovery Collected**: Sum of all recovery amounts from `market_vouchers` table
- **Total Distributed to Banks**: Sum of all amounts in `recovery_bank_distributions` table
- **Pending Distribution**: Difference between collected and distributed (available for next distribution)
- **Total Customers**: Count of unique customers with recovery > 0

---

## Database Tables Used

### 1. `market_vouchers`
```sql
-- Already existing table
-- Used fields: voucher_date, customerArea, customerCode, description, recovery
```

### 2. `recovery_bank_distributions` (NEW)
```sql
-- Stores all bank distribution records
Columns:
- id (primary key)
- voucher_id (foreign key to market_vouchers) - Optional for linking
- bank_account_code (foreign key to bank_accounts)
- amount (decimal)
- reference (string) - Optional note/reference
- created_at / updated_at
- ledger_id (Optional for ledger entry tracking)
```

---

## Routes

| Method | Route | Function | Name |
|--------|-------|----------|------|
| GET | `/recovery-bank-distribution` | Display page | `recovery.distribution` |
| GET | `/recovery/summary` | Get summary statistics | `recovery.summary` |
| GET | `/recovery/list` | Get recovery entries | `recovery.list` |
| GET | `/recovery/distributions` | Get bank distributions | `recovery.distributions` |
| POST | `/recovery/distribute` | Create distribution | `recovery.distribute` |
| DELETE | `/recovery/{id}` | Delete recovery entry | `recovery.delete` |
| DELETE | `/recovery/distribution/{id}` | Delete distribution | `recovery.distribution.delete` |

---

## Controller: RecoveryBankDistributionController

### Methods:

#### 1. `index()`
- Loads the main page
- Passes bank accounts and areas for dropdowns

#### 2. `getSummary()`
- Returns JSON with:
  - total_recovery
  - total_distributed
  - pending_distribution
  - total_customers

#### 3. `getRecoveryList(Request $request)`
- Filters by area (optional)
- Filters by date (optional)
- Returns recovery vouchers with recovery > 0

#### 4. `getDistributions(Request $request)`
- Filters by bank account (optional)
- Filters by date (optional)
- Returns bank distribution records with bank name

#### 5. `distributeRecovery(Request $request)`
- Validates:
  - recovery_amount (numeric, min 0.01)
  - bank_account (must exist in bank_accounts)
  - reference (optional)
- Checks if amount doesn't exceed available pending distribution
- Creates new RecoveryBankDistribution record

#### 6. `deleteRecovery($id)`
- Deletes a recovery voucher entry
- Prevents deletion if distributions already exist for that recovery

#### 7. `deleteDistribution($id)`
- Deletes a distribution record
- Frees up the amount for redistribution

---

## Frontend Features

### Tabs
1. **Recovery Collection Tab**
   - Table showing all recoveries
   - Filter by Area and Date
   - Delete button for each entry

2. **Bank Distribution Tab**
   - Table showing all distributions
   - Filter by Bank and Date
   - Delete button for each distribution

### Summary Cards
- Real-time statistics
- Currency formatted display
- Color-coded icons (green for recovery, blue for bank, yellow for pending)

### AJAX Operations
- All data loads dynamically without page refresh
- Forms submit via AJAX
- Filters work without page reload
- Confirmation modals for deletions

---

## Usage Example

### Scenario
1. Customer A pays recovery of 1000 PKR on Jan 5
2. Customer B pays recovery of 500 PKR on Jan 6
3. Customer C pays recovery of 1500 PKR on Jan 7
4. **Total Collected**: 3000 PKR (all in "Pending Distribution")

**Distribution Process:**
- On Jan 10, you want to send 2000 PKR to Bank A → Enter 2000, select Bank A
- On Jan 12, you want to send remaining 1000 PKR to Bank B → Enter 1000, select Bank B
- **Total Distributed**: 3000 PKR
- **Pending**: 0 PKR

---

## Validation Rules

### Distribution
- Amount must be > 0
- Amount cannot exceed available pending distribution
- Bank account must exist in system
- Reference is optional

### Deletion
- Recovery entry can be deleted only if no distributions link to it
- Distribution records can be freely deleted (reverses that amount to pending)

---

## Files Created/Modified

1. **Created: `/resources/views/Pages/customerAccount/recoveryBankDistribution.blade.php`**
   - Main view file

2. **Created: `/app/Http/Controllers/RecoveryBankDistributionController.php`**
   - Controller with all logic

3. **Modified: `/routes/web.php`**
   - Added all recovery distribution routes

---

## Notes

- The system uses `RecoveryVoucher` model (already exists) for source data
- New `RecoveryBankDistribution` model stores distribution records
- All amounts are in system currency (appears to be PKR based on file names)
- Dates are formatted in 'en-PK' locale (Pakistani date format)
- Real-time updates via AJAX for smooth UX
