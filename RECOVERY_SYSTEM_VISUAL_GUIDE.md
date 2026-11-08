# Recovery Bank Distribution System - Visual Guide

## 1. SYSTEM ARCHITECTURE DIAGRAM

```
┌────────────────────────────────────────────────────────────────┐
│                  RECOVERY BANK DISTRIBUTION SYSTEM             │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  FRONTEND LAYER                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │ recoveryBankDistribution.blade.php                       │ │
│  │ ├─ Summary Cards (4 stats)                               │ │
│  │ ├─ Distribution Form                                     │ │
│  │ └─ Two Tabs                                              │ │
│  │    ├─ Recovery Collection (Table + Filters)              │ │
│  │    └─ Bank Distributions (Table + Filters)               │ │
│  └──────────────────────────────────────────────────────────┘ │
│                              │                                 │
│                    (AJAX Calls)                                │
│                              │                                 │
│  API ROUTES LAYER                                              │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │ GET  /recovery-bank-distribution  ─> index()             │ │
│  │ GET  /recovery/summary             ─> getSummary()       │ │
│  │ GET  /recovery/list                ─> getRecoveryList()  │ │
│  │ GET  /recovery/distributions       ─> getDistributions()│ │
│  │ POST /recovery/distribute          ─> distribute()       │ │
│  │ DEL  /recovery/{id}                ─> deleteRecovery()   │ │
│  │ DEL  /recovery/distribution/{id}   ─> deleteDist()       │ │
│  └──────────────────────────────────────────────────────────┘ │
│                              │                                 │
│                (RecoveryBankDistributionController)            │
│                              │                                 │
│  MODEL/DATABASE LAYER                                          │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │ RecoveryVoucher (market_vouchers table)                  │ │
│  │ ├─ Read: recovery amounts from different customers       │ │
│  │ ├─ Fields: customerCode, customerArea, recovery, date    │ │
│  │ └─ Operations: Get all, Filter by area, Filter by date   │ │
│  │                                                           │ │
│  │ RecoveryBankDistribution (recovery_bank_distributions)   │ │
│  │ ├─ Create: new distribution records                      │ │
│  │ ├─ Fields: bank_account_code, amount, reference, date    │ │
│  │ └─ Operations: Create, Read, Delete                      │ │
│  │                                                           │ │
│  │ bankAccount (bank_accounts table)                        │ │
│  │ ├─ Read: available bank accounts                         │ │
│  │ └─ Filters: Select destination for distribution          │ │
│  └──────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────┘
```

---

## 2. DATA FLOW DIAGRAM

### A. Recovery Collection Flow

```
Customer Pays Recovery
         │
         ▼
Market Voucher Created
(recovery field = amount)
         │
         ▼
RecoveryVoucherController
Saves to market_vouchers table
         │
         ▼
Total Recovery Available
= SUM(market_vouchers.recovery)
         │
         ▼
Shown in "Total Recovery Collected" Card
```

### B. Distribution Flow

```
User Selects Amount & Bank
         │
         ▼
Form Validation
├─ Amount > 0?
├─ Amount ≤ Pending?
└─ Bank exists?
         │
         ▼
Create RecoveryBankDistribution
    Record
         │
         ▼
Saved to recovery_bank_distributions
         │
         ▼
Total Distributed
= SUM(recovery_bank_distributions.amount)
         │
         ▼
Pending Distribution
= Total Recovery - Total Distributed
         │
         ▼
Updated Stats shown in Cards
```

---

## 3. DATABASE SCHEMA

### market_vouchers (Existing)
```
┌─────────────────────────────────────┐
│         market_vouchers             │
├─────────────────────────────────────┤
│ id              INT PRIMARY KEY     │
│ voucher_date    DATE                │ ◄─ Filter by date
│ customerArea    VARCHAR             │ ◄─ Filter by area
│ customerCode    VARCHAR             │ ◄─ Which customer
│ description     VARCHAR             │
│ recovery        DECIMAL(12,2)       │ ◄─ Recovery amount
│ discount        DECIMAL(12,2)       │
│ misc            DECIMAL(12,2)       │
│ preBalance      DECIMAL(12,2)       │
│ lastBalance     DECIMAL(12,2)       │
│ created_at      TIMESTAMP           │
│ updated_at      TIMESTAMP           │
└─────────────────────────────────────┘
         ▲
         │ One-to-Many
         │
┌────────────────────────────────────────────┐
│  recovery_bank_distributions (NEW)         │
├────────────────────────────────────────────┤
│ id                 BIGINT PRIMARY KEY      │
│ voucher_id         BIGINT FOREIGN KEY      │ ◄─ Link to recovery
│ bank_account_code  VARCHAR                 │ ◄─ Which bank
│ amount             DECIMAL(12,2)           │ ◄─ Distribution amount
│ reference          VARCHAR                 │ ◄─ Notes/Reference
│ ledger_id          BIGINT                  │
│ created_at         TIMESTAMP               │ ◄─ When distributed
│ updated_at         TIMESTAMP               │
└────────────────────────────────────────────┘
         │
         │ Many-to-One
         ▼
┌─────────────────────────────────────┐
│      bank_accounts (Existing)       │
├─────────────────────────────────────┤
│ id              INT PRIMARY KEY     │
│ bankAccount     VARCHAR (Key)       │
│ bankName        VARCHAR             │
│ branchCode      VARCHAR             │
│ ...                                 │
└─────────────────────────────────────┘
```

---

## 4. USER INTERFACE FLOW

```
┌─────────────────────────────────────────────────┐
│     RECOVERY BANK DISTRIBUTION PAGE             │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │ Summary Cards Section                   │   │
│  ├─────────────────────────────────────────┤   │
│  │ Total    │ Total    │ Pending │ Total   │   │
│  │ Recovery │Distrib.  │Distrib. │Customers│   │
│  │ 100,000  │  40,000  │  60,000 │   3     │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │ Distribution Form                       │   │
│  ├─────────────────────────────────────────┤   │
│  │ Amount: [___________]                   │   │
│  │         Available: 60,000                │   │
│  │ Bank:   [Select Bank ▼]                 │   │
│  │ Note:   [___________]                   │   │
│  │         [Distribute Button]              │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │ [Recovery Collection] [Bank Distribution]   │
│  │────────────────────────────────────────│   │
│  │                                         │   │
│  │ Filters: Area [▼] Date [____]           │   │
│  │                                         │   │
│  │ ┌───────────────────────────────────┐  │   │
│  │ │Date│Area│Customer│Desc│Amount│Del││  │   │
│  │ ├───────────────────────────────────┤  │   │
│  │ │1/5│Khi │C001    │Pay │50,000│✕ ││  │   │
│  │ │1/6│Lhr │C002    │Pay │30,000│✕ ││  │   │
│  │ │1/7│Khi │C003    │Pay │20,000│✕ ││  │   │
│  │ └───────────────────────────────────┘  │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 5. BUSINESS LOGIC FLOW

### A. Calculating Available Distribution

```
Available for Distribution = Total Collected - Already Distributed

Example:
    Recovery from Customer A  = 50,000
    Recovery from Customer B  = 30,000
    Recovery from Customer C  = 20,000
    ─────────────────────────────────────
    Total Collected           = 100,000

    Distribution 1 to Bank A  = 60,000
    ─────────────────────────────────────
    Already Distributed       = 60,000

    Available for Next        = 100,000 - 60,000 = 40,000
    
    Maximum you can distribute next = 40,000
```

### B. Deletion Impact

```
DELETE Recovery Entry:
├─ Only allowed if NO distributions exist for it
└─ Prevents orphaned distribution records

DELETE Distribution Entry:
├─ Always allowed
├─ Amount goes back to "Available/Pending"
└─ Can be re-distributed to another bank

Example:
    If you delete a 25,000 distribution:
    Available increases by 25,000
    Pending + 25,000 becomes available again
```

---

## 6. API RESPONSE EXAMPLES

### Summary Endpoint Response
```json
{
  "total_recovery": 100000.00,
  "total_distributed": 40000.00,
  "pending_distribution": 60000.00,
  "total_customers": 3
}
```

### Recovery List Response
```json
[
  {
    "id": 1,
    "voucher_date": "2026-01-05",
    "customerArea": "Karachi",
    "customerCode": "C001",
    "description": "Recovery Payment",
    "recovery": 50000.00
  },
  {
    "id": 2,
    "voucher_date": "2026-01-06",
    "customerArea": "Lahore",
    "customerCode": "C002",
    "description": "Recovery Payment",
    "recovery": 30000.00
  }
]
```

### Distribution List Response
```json
[
  {
    "id": 1,
    "bank_account_code": "0001",
    "bank_name": "HBL",
    "amount": 60000.00,
    "reference": "Daily Deposit",
    "created_at": "2026-01-08T10:30:00Z"
  }
]
```

---

## 7. VALIDATION RULES

```
Distribution Creation:
├─ recovery_amount
│  ├─ Type: numeric
│  ├─ Min: 0.01
│  ├─ Max: Pending Distribution amount
│  └─ Error: "cannot exceed available recovery amount"
│
├─ bank_account
│  ├─ Required: YES
│  ├─ Must exist in: bank_accounts table
│  └─ Error: "selected bank is invalid"
│
└─ reference (optional)
   ├─ Type: string
   └─ Max: 255 characters

Deletion:
├─ Recovery Entry
│  ├─ Only if voucher_id has NO distributions
│  └─ Error: "Cannot delete recovery with distributions"
│
└─ Distribution Entry
   ├─ Always allowed
   └─ Frees up the amount for re-distribution
```

---

## 8. EXAMPLE SCENARIOS

### Scenario 1: Simple Distribution
```
Step 1: Customer recoveries
├─ Customer A: 50,000 (Area: Karachi)
└─ Customer B: 50,000 (Area: Lahore)
Total Available: 100,000

Step 2: Distribute to HBL
├─ Amount: 100,000
├─ Bank: HBL (0001)
├─ Reference: "Monthly Transfer"
└─ Status: ✓ Done

Step 3: Summary
├─ Total Collected: 100,000
├─ Total Distributed: 100,000
├─ Pending: 0
└─ Status: Fully distributed
```

### Scenario 2: Multiple Distributions
```
Step 1: Customer recoveries
├─ Customer A: 30,000
├─ Customer B: 40,000
├─ Customer C: 30,000
Total Available: 100,000

Step 2: Distribute to HBL
├─ Amount: 60,000
└─ Status: ✓ Done

Step 3: Distribute to NBP
├─ Amount: 40,000
└─ Status: ✓ Done

Step 4: Summary
├─ Total Collected: 100,000
├─ Total Distributed: 100,000
├─ Pending: 0
└─ Status: Fully distributed (2 banks)
```

### Scenario 3: Delete & Re-distribute
```
Step 1: Distribution made
├─ Amount: 30,000 to Bank A
└─ Pending: 70,000

Step 2: Delete distribution
├─ Removed 30,000 from Bank A
└─ Pending: 100,000 (returns to available)

Step 3: Re-distribute differently
├─ Amount: 30,000 to Bank B (instead)
└─ Pending: 70,000
```

---

## 9. REFERENCE GUIDE

### Key Tables
| Table | Purpose | Key Field |
|-------|---------|-----------|
| market_vouchers | Store recovery source | recovery amount |
| recovery_bank_distributions | Store distributions | bank_account_code, amount |
| bank_accounts | Available banks | bankAccount (key) |
| customers | Customer info | custCode, custArea |

### Key Routes
| Route | Returns |
|-------|---------|
| /recovery-bank-distribution | Page |
| /recovery/summary | JSON stats |
| /recovery/list | JSON array of recoveries |
| /recovery/distributions | JSON array of distributions |

### Key Calculations
```
Total Recovery = SUM(market_vouchers.recovery WHERE recovery > 0)
Total Distributed = SUM(recovery_bank_distributions.amount)
Pending = Total Recovery - Total Distributed
```

---

This visual guide should help you understand how the Recovery Bank Distribution System works!
