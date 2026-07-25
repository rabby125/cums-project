import re
import sys
import json
from netmiko import ConnectHandler
from netmiko.exceptions import NetmikoTimeoutException, NetmikoAuthenticationException
from vendor_commands import (
    get_device_type, get_verify_command,
    get_create_commands, get_delete_commands, get_reset_password_commands
)
from vendor_commands import (
    get_device_type, get_verify_command, get_list_users_command,
    get_create_commands, get_delete_commands, get_reset_password_commands
)

def connect_device(ip, username, password, vendor, port=22):
    device_type = get_device_type(vendor)
    if not device_type:
        return None, f"Unsupported vendor: {vendor}"
    device = {"device_type": device_type, "ip": ip, "username": username,
              "password": password, "port": port, "timeout": 10}
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
        cmd = get_verify_command(vendor)
        output = connection.send_command(cmd)
        connection.disconnect()
        if target_username in output:
            return {"status": "success", "message": "User found", "output": output}
        else:
            return {"status": "not_found", "message": "User not found on device"}
    except Exception as e:
        return {"status": "error", "message": f"Command execution failed: {str(e)}"}

    import re

def list_users(ip, username, password, vendor, port=22):
    connection, error = connect_device(ip, username, password, vendor, port)
    if error:
        return {"status": "error", "message": error}
    try:
        cmd = get_list_users_command(vendor)
        output = connection.send_command(cmd)
        connection.disconnect()

        vendor_l = vendor.lower()
        usernames = []
        if vendor_l in ("cisco", "arista"):
            usernames = re.findall(r'username\s+(\S+)', output)
        elif vendor_l == "huawei":
            usernames = re.findall(r'local-user\s+(\S+)', output)
        elif vendor_l == "mikrotik":
            usernames = re.findall(r'name=("[^"]+"|\S+)', output)
            usernames = [u.strip('"') for u in usernames]
        elif vendor_l == "juniper":
            usernames = re.findall(r'user\s+(\S+)\s+class', output)

        usernames = sorted(set(usernames))
        return {"status": "success", "message": f"{len(usernames)} user(s) found", "users": usernames, "raw_output": output}
    except Exception as e:
        return {"status": "error", "message": f"List failed: {str(e)}"}

def create_user(ip, username, password, vendor, target_username, target_password, port=22):
    connection, error = connect_device(ip, username, password, vendor, port)
    if error:
        return {"status": "error", "message": error}
    try:
        commands = get_create_commands(vendor, target_username, target_password)
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
        commands = get_delete_commands(vendor, target_username)
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
        commands = get_reset_password_commands(vendor, target_username, new_password)
        output = connection.send_config_set(commands)
        connection.disconnect()
        return {"status": "success", "message": "Password reset", "output": output}
    except Exception as e:
        return {"status": "error", "message": f"Reset failed: {str(e)}"}

if __name__ == "__main__":
    if len(sys.argv) < 7:
        print(json.dumps({"status": "error", "message": "Missing arguments"}))
        sys.exit(1)

    action = sys.argv[1]
    ip, uname, pwd, vendor, target = sys.argv[2:7]
    
    # পোর্ট আছে কিনা চেক (সবার শেষ আর্গুমেন্ট হিসেবে --port=XXXX ফরম্যাটে)
    port = 22
    args = sys.argv[7:]
    filtered_args = []
    for a in args:
        if a.startswith("--port="):
            port = int(a.replace("--port=", ""))
        else:
            filtered_args.append(a)

    if action == "verify":
        result = verify_user(ip, uname, pwd, vendor, target, port=port)
    elif action == "create":
        target_pwd = filtered_args[0] if len(filtered_args) > 0 else "default123"
        result = create_user(ip, uname, pwd, vendor, target, target_pwd, port=port)
    elif action == "delete":
        result = delete_user(ip, uname, pwd, vendor, target, port=port)
    elif action == "reset":
        new_pwd = filtered_args[0] if len(filtered_args) > 0 else "newpass123"
        result = reset_password(ip, uname, pwd, vendor, target, new_pwd, port=port)
    elif action == "list":
        result = list_users(ip, uname, pwd, vendor, port=port)
    else:
        result = {"status": "error", "message": f"Unknown action: {action}"}

    print(json.dumps(result))