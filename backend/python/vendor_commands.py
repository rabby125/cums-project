# প্রতিটা ভেন্ডরের জন্য Netmiko device_type ম্যাপিং
VENDOR_DEVICE_TYPE = {
    "huawei": "huawei",
    "cisco": "cisco_ios",
    "mikrotik": "mikrotik_routeros",
    "juniper": "juniper_junos",
    "arista": "arista_eos"
}

def get_device_type(vendor_name):
    return VENDOR_DEVICE_TYPE.get(vendor_name.lower(), None)