# Testing Guide - Recovery Bank Distribution System

## 🧪 Pre-Launch Checklist

### Before Testing
- [ ] Migration has run: `php artisan migrate`
- [ ] Routes are registered: Check `routes/web.php`
- [ ] Controller exists: `RecoveryBankDistributionController.php`
- [ ] View exists: `recoveryBankDistribution.blade.php`
- [ ] Bank accounts exist in system
- [ ] Recovery data exists in market_vouchers

---

## ✅ Test Plan

### Test 1: Page Load
**Steps:**
1. Navigate to `/recovery-bank-distribution`

**Expected:**
- ✓ Page loads without errors
- ✓ 4 summary cards appear (empty = 0)
- ✓ Distribution form is visible
- ✓ 2 tabs appear (Recovery & Distribution)
- ✓ Tables are empty with "No data" message

**Result:** PASS / FAIL

---

### Test 2: Load Summary Data
**Setup:** Ensure some recovery data exists in market_vouchers

**Steps:**
1. Go to page
2. Check summary cards

**Expected:**
- ✓ Total Recovery > 0 (if data exists)
- ✓ Total Distributed = 0 (initial)
- ✓ Pending = Total Recovery
- ✓ Total Customers > 0

**Result:** PASS / FAIL

**Example:**
```
If in database:
- Customer A: 50,000 recovery
- Customer B: 30,000 recovery
- Customer C: 20,000 recovery

Then cards should show:
- Total Recovery: 100,000.00
- Total Distributed: 0.00
- Pending: 100,000.00
- Customers: 3
```

---

### Test 3: Recovery Collection Tab
**Steps:**
1. Click "Recovery Collection" tab
2. Observe table

**Expected:**
- ✓ Table shows all recoveries with recovery > 0
- ✓ Columns: Date | Area | Customer | Description | Amount | Delete
- ✓ Data is correct from database

**Sample Data:**
```
Date   | Area    | Customer | Desc | Amount  | Action
-------|---------|----------|------|---------|--------
1/5/26 | Karachi | C001     | Pay  | 50,000  | ✕
1/6/26 | Lahore  | C002     | Pay  | 30,000  | ✕
1/7/26 | Karachi | C003     | Pay  | 20,000  | ✕
```

**Result:** PASS / FAIL

---

### Test 4: Filter by Area
**Steps:**
1. Recovery Collection tab
2. Select "Karachi" from Area filter
3. Observe table

**Expected:**
- ✓ Table updates instantly
- ✓ Shows only Karachi customers
- ✓ Should show 2 rows (C001, C003)

**Result:** PASS / FAIL

---

### Test 5: Filter by Date
**Steps:**
1. Recovery Collection tab
2. Select a specific date (1/5/26)
3. Observe table

**Expected:**
- ✓ Table shows only that date
- ✓ Shows only 1 row for that date

**Result:** PASS / FAIL

---

### Test 6: Distribution Form - Valid Entry
**Steps:**
1. Enter: Amount = 30000
2. Select: Bank = HBL
3. Enter: Reference = "Test deposit"
4. Click: Distribute to Bank

**Expected:**
- ✓ No error message
- ✓ Success notification appears
- ✓ Form clears
- ✓ Summary cards update:
  - Total Distributed: 30,000
  - Pending: 70,000

**Result:** PASS / FAIL

---

### Test 7: Distribution Form - Invalid Entry (Too Much)
**Steps:**
1. Pending amount is 70,000
2. Enter: Amount = 100000 (more than pending)
3. Select: Bank = HBL
4. Click: Distribute to Bank

**Expected:**
- ✓ Error message appears
- ✓ Message: "cannot exceed available recovery amount"
- ✓ Distribution NOT created

**Result:** PASS / FAIL

---

### Test 8: Distribution Form - Invalid Bank
**Steps:**
1. Enter: Amount = 20000
2. Select: Invalid/Non-existent bank
3. Click: Distribute to Bank

**Expected:**
- ✓ Error message
- ✓ Message: "selected bank is invalid"
- ✓ Distribution NOT created

**Result:** PASS / FAIL

---

### Test 9: Bank Distribution Tab
**Steps:**
1. Create at least one distribution (Test 6)
2. Click "Bank Distributions" tab
3. Observe table

**Expected:**
- ✓ Shows distribution(s)
- ✓ Columns: Date | Bank Code | Bank Name | Amount | Reference | Status | Delete
- ✓ Data is correct

**Sample:**
```
Date   | Bank Code | Bank Name | Amount | Reference    | Status | Action
-------|-----------|-----------|--------|--------------|--------|--------
1/8/26 | 0001      | HBL       | 30,000 | Test deposit | Done   | ✕
```

**Result:** PASS / FAIL

---

### Test 10: Filter Distributions by Bank
**Steps:**
1. Bank Distributions tab
2. Create distributions for 2 different banks
3. Filter by one bank

**Expected:**
- ✓ Table shows only selected bank
- ✓ Other bank's distribution hidden

**Result:** PASS / FAIL

---

### Test 11: Delete Distribution
**Steps:**
1. Bank Distributions tab
2. Click ✕ on any distribution
3. Confirm deletion

**Expected:**
- ✓ Confirmation modal appears
- ✓ Distribution removed from table
- ✓ Success message shown
- ✓ Summary cards update:
  - Total Distributed decreases
  - Pending increases

**Verify:**
```
Before: Total=100k, Distributed=30k, Pending=70k
Delete: 30k distribution
After:  Total=100k, Distributed=0k, Pending=100k
```

**Result:** PASS / FAIL

---

### Test 12: Cancel Distribution Deletion
**Steps:**
1. Click ✕ on distribution
2. In confirmation modal, click "Cancel"

**Expected:**
- ✓ Modal closes
- ✓ Distribution still in table
- ✓ No data changed

**Result:** PASS / FAIL

---

### Test 13: Delete Recovery Entry
**Steps:**
1. Recovery Collection tab
2. Click ✕ on any recovery entry

**Expected (If no distributions):**
- ✓ Confirmation appears
- ✓ Entry deleted
- ✓ Table updates
- ✓ Summary cards update

**Expected (If distributions exist):**
- ✓ Error message: "Cannot delete recovery with distributions"
- ✓ Entry NOT deleted

**Result:** PASS / FAIL

---

### Test 14: Complete Distribution Cycle
**Steps:**
1. Start: Total = 100,000 | Pending = 100,000
2. Distribute: 60,000 to Bank A
3. Distribute: 40,000 to Bank B
4. Check summary

**Expected:**
- ✓ Total: 100,000 (unchanged)
- ✓ Distributed: 100,000
- ✓ Pending: 0
- ✓ Bank Distribution tab shows 2 entries

**Result:** PASS / FAIL

---

### Test 15: Re-distribute After Delete
**Steps:**
1. After Test 14 (Pending = 0)
2. Delete one distribution (40,000 from Bank B)
3. Create new distribution to Bank C with 40,000

**Expected:**
- ✓ First distribution deleted
- ✓ Pending goes to 40,000
- ✓ New distribution created to Bank C
- ✓ Pending back to 0

**Result:** PASS / FAIL

---

### Test 16: AJAX Loading
**Steps:**
1. Go to page
2. Observe network requests
3. Filter data (should use AJAX)

**Expected:**
- ✓ No page refresh
- ✓ Data updates smoothly
- ✓ Network shows AJAX calls
- ✓ Status 200 OK

**Result:** PASS / FAIL

---

### Test 17: Responsive Design
**Steps:**
1. Test on Desktop (1920px)
2. Test on Tablet (768px)
3. Test on Mobile (375px)

**Expected (All screens):**
- ✓ Page loads properly
- ✓ Cards stack appropriately
- ✓ Tables are readable
- ✓ Forms are usable
- ✓ Buttons are clickable

**Result:** PASS / FAIL

---

### Test 18: Error Handling
**Steps:**
1. Try to submit form with invalid data
2. Check console for errors
3. Try deleting with no data

**Expected:**
- ✓ Graceful error messages
- ✓ No JavaScript errors
- ✓ No white screen
- ✓ User-friendly messages

**Result:** PASS / FAIL

---

### Test 19: Data Persistence
**Steps:**
1. Create distribution
2. Refresh page
3. Check if data still there

**Expected:**
- ✓ Data persists
- ✓ Summary correct
- ✓ Distributions still visible

**Result:** PASS / FAIL

---

### Test 20: Currency Formatting
**Steps:**
1. Create distribution with 12345.67
2. Check display format

**Expected:**
- ✓ Shows as: 12345.67
- ✓ 2 decimal places always
- ✓ Proper thousands separator (if applicable)

**Result:** PASS / FAIL

---

## 🐛 Bug Reporting Template

If you find a bug, use this template:

```
Bug #: ___
Title: ___

Steps to Reproduce:
1. 
2. 
3. 

Expected Behavior:
-

Actual Behavior:
-

Screenshot:
[Attach if possible]

Database State:
Total Recovery: _____
Total Distributed: _____
Pending: _____

Browser: ___________
OS: ___________

Severity: Critical / High / Medium / Low
```

---

## 📊 Test Results Summary

| Test # | Description | Status | Notes |
|--------|-------------|--------|-------|
| 1 | Page Load | PASS/FAIL | |
| 2 | Load Summary | PASS/FAIL | |
| 3 | Recovery Tab | PASS/FAIL | |
| 4 | Filter Area | PASS/FAIL | |
| 5 | Filter Date | PASS/FAIL | |
| 6 | Valid Distribution | PASS/FAIL | |
| 7 | Exceed Amount | PASS/FAIL | |
| 8 | Invalid Bank | PASS/FAIL | |
| 9 | Distrib Tab | PASS/FAIL | |
| 10 | Filter Bank | PASS/FAIL | |
| 11 | Delete Distrib | PASS/FAIL | |
| 12 | Cancel Delete | PASS/FAIL | |
| 13 | Delete Recovery | PASS/FAIL | |
| 14 | Complete Cycle | PASS/FAIL | |
| 15 | Re-distribute | PASS/FAIL | |
| 16 | AJAX Loading | PASS/FAIL | |
| 17 | Responsive | PASS/FAIL | |
| 18 | Error Handling | PASS/FAIL | |
| 19 | Data Persist | PASS/FAIL | |
| 20 | Currency Format | PASS/FAIL | |

**Overall Status:** __________

---

## 🚀 Go-Live Checklist

Before launching to production:

- [ ] All 20 tests passed
- [ ] Migration successfully ran
- [ ] Routes properly registered
- [ ] Bank accounts configured
- [ ] Sample data tested
- [ ] Performance acceptable
- [ ] Error messages user-friendly
- [ ] Mobile responsive confirmed
- [ ] CSRF tokens working
- [ ] Data validation strict
- [ ] Documentation reviewed
- [ ] Team trained
- [ ] Backup of database taken

---

## 🎓 Manual Testing Scenarios

### Scenario A: Daily Operations
```
9 AM: View summary → See 0 pending
10 AM: Recoveries come in → Total increases
3 PM: Distribute to Bank A → Pending decreases
5 PM: Final summary → All distributed
```

### Scenario B: Error Handling
```
1. Try to distribute 200k when pending is 100k → Error
2. Try to delete recovery with distribution → Error
3. Try invalid bank → Error
4. All errors handled gracefully
```

### Scenario C: Busy Day
```
1. Many recoveries come in
2. Multiple distributions made
3. Some distributions deleted
4. Final balance is correct
```

---

## 📝 Notes

- Tests should be run in order
- Each test builds on previous data
- Use same data throughout for consistency
- Document any issues found
- Take screenshots of failures

---

**Ready to test? Start with Test 1! 🧪**
