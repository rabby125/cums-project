import sys
import json
from netmiko import ConnectHandler
from netmiko.exceptions import NetmikoTimeoutException, NetmikoAuthenticationException
from vendor_commands import get_device_type

def connect_device(ip, username, password, vendor, port=22):
    device_type = get_device_type(vendor)
    if not device_type:
        return None, f"Unsupported vendor: {vendor}"

    device = {
        "device_type": device_type,
        "ip": ip,
        "username": username,
        "password": password,
        "port": port,
        "timeout": 10,
    }

    try:
        connection = ConnectHandler(**device)
        return connection, None
    except NetmikoTimeoutException:
        return None, "Device unreachable (timeout)"
    except NetmikoAuthenticationException:
        return None, "Authentication failed (wrong credentials)"
    except Exception as e:
        return None, f"Connection error: {str(e)}"


def verify_user(ip, username, password, vendor, target_username, port=22):
    connection, error = connect_device(ip, username, password, vendor, port)
    if error:
        return {"status": "error", "message": error}

    try:
        # আপাতত সব ভেন্ডরের জন্য একটা জেনেরিক টেস্ট কমান্ড (পরে ভেন্ডর-স্পেসিফিক করব)
        output = connection.send_command("show run | include username")
        connection.disconnect()

        if target_username in output:
            return {"status": "success", "message": "User found", "output": output}
        else:
            return {"status": "not_found", "message": "User not found on device"}
    except Exception as e:
        return {"status": "error", "message": f"Command execution failed: {str(e)}"}


if __name__ == "__main__":
    # কমান্ড লাইন থেকে টেস্ট করার জন্য: python ssh_engine.py <ip> <username> <password> <vendor> <target_username>
    if len(sys.argv) < 6:
        print(json.dumps({"status": "error", "message": "Missing arguments"}))
        sys.exit(1)

    ip, uname, pwd, vendor, target = sys.argv[1:6]
    result = verify_user(ip, uname, pwd, vendor, target)
    print(json.dumps(result))