# Central User Management System (CUMS)

একটি রিয়েল-টাইম নেটওয়ার্ক ইউজার ম্যানেজমেন্ট সিস্টেম, যা মাল্টি-ভেন্ডর নেটওয়ার্ক ডিভাইসে (Huawei, Cisco, MikroTik, Juniper, Arista) SSH এর মাধ্যমে সরাসরি ইউজার Create/Edit/Delete/Verify করতে পারে। এই সিস্টেমে কোনো কেন্দ্রীয় ইউজার ডাটাবেজ নেই — ইউজাররা সরাসরি নেটওয়ার্ক ডিভাইসেই থাকে।

## Tech Stack
- **Backend:** PHP (REST API)
- **Automation Engine:** Python (Netmiko/Paramiko দিয়ে SSH)
- **Database:** MySQL (শুধু devices, admins, activity_logs, vendor_commands, settings টেবিল)
- **Frontend:** HTML, CSS, JavaScript

## মূল ফিচার
- Admin লগইন (session-based authentication)
- Zone/Device অনুযায়ী ফিল্টার করা ডিভাইস তালিকা
- রিয়েল-টাইম SSH দিয়ে ইউজার ভেরিফিকেশন
- Create / Delete / Reset Password অপারেশন
- Activity Logs (প্রতিটা অপারেশনের রেকর্ড)
- মাল্টি-ভেন্ডর সাপোর্ট (৫টা ভেন্ডরের জন্য আলাদা কমান্ড সেট)
- SSH ক্রেডেনশিয়াল এনক্রিপ্টেড আকারে সংরক্ষণ

## ইনস্টলেশন
1. XAMPP ইনস্টল করুন এবং Apache + MySQL চালু করুন
2. এই রিপোজিটরি `htdocs` ফোল্ডারে ক্লোন করুন
3. phpMyAdmin এ `cums_db` নামে ডাটাবেজ বানিয়ে `database/schema.sql` ইমপোর্ট করুন
4. `backend/python` ফোল্ডারে গিয়ে `pip install -r requirements.txt` চালান
5. ব্রাউজারে `http://localhost/cums-project/frontend/index.html` এ যান
6. ডিফল্ট লগইন: `admin` / `admin123`

## সীমাবদ্ধতা / ভবিষ্যৎ উন্নয়ন
- বর্তমানে বাস্তব Huawei/Cisco/MikroTik ডিভাইসে টেস্ট করা হয়নি (GNS3/EVE-NG ল্যাব প্রয়োজন); SSH কানেকশন ইঞ্জিন ভেরিফাই করা হয়েছে
- ভেন্ডর কমান্ড সেট অফিসিয়াল ডকুমেন্টেশন অনুযায়ী লেখা, বাস্তব ডিভাইসে ফাইন-টিউনিং প্রয়োজন হতে পারে