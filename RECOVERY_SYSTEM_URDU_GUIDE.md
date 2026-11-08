# Recovery Bank Distribution System - اردو وضاحت

## 📌 مختصر وضاحت (Summary)

آپ کا تقاضہ یہ تھا:
```
Area سے مختلف customer سے Recovery payment ہوتی ہے۔
اس کو ہر customer سے delete کر دینا ہے۔
لیکن مسئلہ یہ ہے کہ ہر entry bank میں نہیں جاتی۔
بلکہ کسی جگہ اس کا total ہوتا رہے۔
اور وہاں سے وہ amount مختلف banks میں distribute کر دو۔
```

### ✅ حل (Solution)

میں نے ایک نیا نظام بنایا ہے جو **بالکل یہی کام کرتا ہے:**

1. **مختلف Areas سے** Recovery پول میں جمع ہوتی ہے
2. **Central میں** سب recovery کا total رہتا ہے
3. **جب ضروری ہو** تو اس total میں سے مختلف banks کو distribute کریں

---

## 🏗️ نیا نظام کا Design

```
┌─────────────────────────────┐
│  Customer A (Karachi)       │
│  Recovery: 50,000           │
└──────────────┬──────────────┘
               │
┌──────────────┴──────────────┐
│  Customer B (Lahore)        │
│  Recovery: 30,000           │
└──────────────┬──────────────┘
               │
┌──────────────┴──────────────┐
│  Customer C (Karachi)       │
│  Recovery: 20,000           │
└──────────────┬──────────────┘
               │
               ▼
       ┌──────────────────┐
       │  CENTRAL POOL    │  ◄─── یہاں سب جمع ہوتے ہیں
       │  Total: 100,000  │
       │  (Pending)       │
       └────────┬─────────┘
                │ جب distribute کرنا ہو
                ▼
       ┌──────────────────┐
       │  Amount: 60,000  │
       │  To: HBL Bank    │
       │  Status: ✓ Done  │
       └──────────────────┘
       
       ┌──────────────────┐
       │  Amount: 40,000  │
       │  To: NBP Bank    │
       │  Status: ✓ Done  │
       └──────────────────┘
```

---

## 📊 اہم Concepts

### 1. Recovery Collection (جمع کرنا)
```
ہر customer سے جو recovery ملتی ہے:
├─ Date: کب کی recovery
├─ Area: کس Area سے (Karachi, Lahore وغیرہ)
├─ Customer: کون سا customer
├─ Amount: کتنی رقم
└─ Status: Collected ✓

سب کو add کریں:
Total Recovery = 100,000
```

### 2. Central Pool (مرکزی جگہ)
```
تمام recovery کو ایک جگہ محفوظ رکھیں:

Total Collected:     100,000  (سب recovery کو جمع)
Total Distributed:   -60,000  (جو بھیج دیا)
─────────────────────────────
Pending/Available:    40,000   (اگلی بار دینے کے لیے)
```

### 3. Bank Distribution (بانکوں کو دینا)
```
جب ready ہوں تو banks کو دیں:

Distribution 1:
├─ Amount: 60,000
├─ Bank: HBL (0001)
├─ Date: 8 Jan 2026
└─ Status: ✓ Done

Distribution 2:
├─ Amount: 40,000
├─ Bank: NBP (0002)
├─ Date: 10 Jan 2026
└─ Status: ✓ Done

Total Distributed: 100,000
Pending: 0
```

---

## 🎯 صفحہ کے حصے (Page Sections)

### A. سمری کارڈز (Summary Cards)
```
┌─────────────────┐  ┌──────────────────┐  ┌────────────────┐  ┌─────────────┐
│ Total Recovery  │  │ Total Distributed│  │ Pending/Available
│                 │  │                  │  │                │  │ Customers   │
│   100,000       │  │     60,000       │  │   40,000       │  │      3      │
└─────────────────┘  └──────────────────┘  └────────────────┘  └─────────────┘
```

### B. Distribution Form (توزیع فارم)
```
Amount: [40000]        ◄─ Pending سے کتنا دینا ہے
Bank:   [HBL Bank]     ◄─ کون سی bank میں
Note:   [Daily memo]   ◄─ اختیاری نوٹ
Button: [Distribute]   ◄─ Confirm کریں
```

### C. دو Tabs (دونوں ٹیبیں)

**Tab 1: Recovery Collection (جمع کی تفصیل)**
```
Date  │ Area    │ Customer │ Amount  │ Delete
──────┼─────────┼──────────┼─────────┼────
1/5   │ Karachi │ C001     │ 50,000  │  ✕
1/6   │ Lahore  │ C002     │ 30,000  │  ✕
1/7   │ Karachi │ C003     │ 20,000  │  ✕
```

**Tab 2: Bank Distributions (بھیجی ہوئی رقم)**
```
Date  │ Bank    │ Amount  │ Reference      │ Delete
──────┼─────────┼─────────┼────────────────┼────
1/8   │ HBL     │ 60,000  │ Daily deposit  │  ✕
1/10  │ NBP     │ 40,000  │ Weekly memo    │  ✕
```

---

## 🔧 کیسے استعمال کریں

### Step 1: صفحہ کھولیں
```
URL: /recovery-bank-distribution
```

### Step 2: Recovery دیکھیں
```
Tab: "Recovery Collection" کھولیں
فلٹر کریں:
├─ Area سے (Karachi, Lahore وغیرہ)
└─ Date سے
```

### Step 3: Distribute کریں
```
اوپر Form میں:
1. Amount ڈالیں (Pending میں سے)
2. Bank منتخب کریں
3. Distribute بٹن دبائیں
```

### Step 4: Verify کریں
```
Tab: "Bank Distributions" میں دیکھیں
کہ رقم بھیج دی گئی یا نہیں
```

---

## 📱 فارم کی تفصیلات

### Distribution Form Validation

```
Amount Field:
├─ ضروری ہے: ہاں
├─ کم سے کم: 0.01
├─ زیادہ سے زیادہ: Pending amount
└─ غلطی: "یہ Pending سے زیادہ ہے"

Bank Field:
├─ ضروری ہے: ہاں
├─ Valid ہونی چاہیے
└─ غلطی: "یہ Bank موجود نہیں"

Note Field:
├─ ضروری: نہیں (اختیاری)
└─ زیادہ سے زیادہ: 255 حروف
```

---

## ⚠️ اہم نکات

### کیا کریں ✓
```
✓ Amount Pending میں سے ہی دیں
✓ Valid Bank منتخب کریں
✓ Reference ہمیشہ دیں (تتبع کے لیے)
✓ روزانہ distribution کریں
✓ Balance کو صفر رکھیں
```

### کیا نہ کریں ✗
```
✗ Pending سے زیادہ رقم نہ دیں
✗ غلط Bank منتخب نہ کریں
✗ بغیر نوٹ کے نہ دیں
✗ ایک Entry کو دو بار نہ دیں
```

---

## 🔄 Delete کرنے کا طریقہ

### Recovery Entry Delete کریں
```
جب صرف اگر:
├─ کوئی distribution نہیں ہے اس کے لیے
└─ یقینی ہوں کہ غلط entry ہے

Tab: Recovery Collection
موجود Entry کے آگے ✕ دبائیں
Confirm کریں
```

### Distribution Delete کریں
```
جب بھی ضرورت ہو:
├─ رقم واپس pending میں آتی ہے
└─ دوسری bank کو دے سکتے ہو

Tab: Bank Distributions
موجود Distribution کے آگے ✕ دبائیں
Confirm کریں
Amount خود بخود Pending میں شامل ہو جاتی ہے
```

---

## 📈 مثال کی کہانی

### دن 1-5: Recovery جمع کرنا

```
دن 1: Customer A (Karachi) → 50,000
      Summary: Total=50,000  Pending=50,000

دن 2: Customer B (Lahore) → 30,000
      Summary: Total=80,000  Pending=80,000

دن 3: Customer C (Karachi) → 20,000
      Summary: Total=100,000  Pending=100,000
```

### دن 6: پہلی بار Distribution

```
فیصلہ: HBL Bank کو 60,000 دیں
Action: Form میں:
        Amount: 60,000
        Bank: HBL
        Note: "ہفتہ وار جمع"

Result:
Total:    100,000 (وہی)
Distributed: 60,000
Pending:  40,000
```

### دن 8: دوسری بار Distribution

```
فیصلہ: NBP Bank کو 40,000 دیں
Action: Form میں:
        Amount: 40,000
        Bank: NBP
        Note: "باقی رقم"

Result:
Total:    100,000 (وہی)
Distributed: 100,000
Pending:  0 (مکمل توزیع)
```

---

## 🛠️ Database کی معلومات

### ٹیبلز

**1. market_vouchers (پہلے سے موجود)**
```
یہ سب recovery کو رکھتا ہے:
├─ voucher_date: کب کی recovery
├─ customerArea: کس Area سے
├─ customerCode: کون سا customer
├─ recovery: کتنی رقم
└─ دوسری معلومات
```

**2. recovery_bank_distributions (نیا)**
```
یہ سب distributions کو رکھتا ہے:
├─ bank_account_code: کون سی Bank
├─ amount: کتنی رقم دی
├─ reference: کیوں دیا
└─ created_at: کب دیا
```

### منطق

```
کل Recovery = market_vouchers میں recovery کا مجموعہ

کل Distributed = recovery_bank_distributions میں amount کا مجموعہ

Pending = کل Recovery - کل Distributed
```

---

## ❓ عام سوالات

### سوال: کیا Recovery Entry delete کر سکتے ہو؟
**جواب:** ہاں، صرف اگر اس کے لیے کوئی distribution نہ ہو۔

### سوال: Distribution delete کرنے سے کیا ہوگا؟
**جواب:** رقم واپس "Pending" میں چلی جاتی ہے۔ دوسری bank کو دے سکتے ہو۔

### سوال: Pending سے زیادہ رقم دے سکتے ہو؟
**جواب:** نہیں، نظام allow نہیں کرے گا۔ Error آئے گی۔

### سوال: ایک Recover کو دو banks کو دے سکتے ہو؟
**جواب:** ہاں، دو الگ distributions بناؤ۔ 50,000 ایک bank کو، 50,000 دوسری کو۔

---

## 📝 خلاصہ

```
پرانا نظام ❌:
Customer A → Bank A
Customer B → Bank B
Customer C → Bank A
(ہر recovery سیدھا bank جاتا تھا)

نیا نظام ✓:
Customer A ─┐
Customer B ─┼─→ Central Pool → Distribute کریں
Customer C ─┘                    ├─ Bank A: 60,000
                                 └─ Bank B: 40,000
```

**بہت بہتری ہے! اب مکمل کنٹرول ہے۔**

---

## 🎓 خلاصہ

میں نے آپ کے لیے یہ بنایا ہے:

✅ **ایک صفحہ** جو Recovery کو manage کرے  
✅ **دو ٹیبیں** - Collection اور Distribution  
✅ **سمری کارڈز** - کل کی معلومات  
✅ **فارم** - آسانی سے Distribute کریں  
✅ **فلٹرز** - Area اور Date سے تلاش کریں  
✅ **Delete** - غلط entries صاف کریں  

**سب کچھ آسان، سمجھنے میں اور استعمال میں!**
