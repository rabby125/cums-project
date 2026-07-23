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

def create_user(ip, username, password, vendor, target_username, target_password, port=22):
    connection, error = connect_device(ip, username, password, vendor, port)
    if error:
        return {"status": "error", "message": error}

    try:
        # আপাতত জেনেরিক প্লেসহোল্ডার কমান্ড — ভেন্ডর অনুযায়ী পরে ঠিক করব
        commands = [f"username {target_username} password {target_password}"]
        output = connection.send_config_set(commands)
        connection.disconnect()
        return {"status": "success", "message": "User created", "output": output}
    except Exception as e:
        return {"status": "error", "message": f"Create failed: {str(e)}"}


def delete_user(ip, username, password, vendor, target_username, port=22):
    connection, error = connect_device(ip, username, password, vendor, port)
    if error:
        return {"status": "error", "message": error}

    try:
        commands = [f"no username {target_username}"]
        output = connection.send_config_set(commands)
        connection.disconnect()
        return {"status": "success", "message": "User deleted", "output": output}
    except Exception as e:
        return {"status": "error", "message": f"Delete failed: {str(e)}"}

def reset_password(ip, username, password, vendor, target_username, new_password, port=22):
    connection, error = connect_device(ip, username, password, vendor, port)
    if error:
        return {"status": "error", "message": error}

    try:
        commands = [f"username {target_username} password {new_password}"]
        output = connection.send_config_set(commands)
        connection.disconnect()
        return {"status": "success", "message": "Password reset", "output": output}
    except Exception as e:
        return {"status": "error", "message": f"Reset failed: {str(e)}"}


if __name__ == "__main__":
    # ব্যবহার: python ssh_engine.py <action> <ip> <ssh_user> <ssh_pass> <vendor> <target_username> [target_password]
    if len(sys.argv) < 7:
        print(json.dumps({"status": "error", "message": "Missing arguments"}))
        sys.exit(1)

    action = sys.argv[1]
    ip, uname, pwd, vendor, target = sys.argv[2:7]

    if action == "verify":
        result = verify_user(ip, uname, pwd, vendor, target)
    elif action == "create":
        target_pwd = sys.argv[7] if len(sys.argv) > 7 else "default123"
        result = create_user(ip, uname, pwd, vendor, target, target_pwd)
    elif action == "delete":
        result = delete_user(ip, uname, pwd, vendor, target)
    elif action == "reset":
        new_pwd = sys.argv[7] if len(sys.argv) > 7 else "newpass123"
        result = reset_password(ip, uname, pwd, vendor, target, new_pwd)
    else:
        result = {"status": "error", "message": f"Unknown action: {action}"}

    print(json.dumps(result))    


