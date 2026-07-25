VENDOR_DEVICE_TYPE = {
    "huawei": "huawei",
    "cisco": "cisco_ios",
    "mikrotik": "mikrotik_routeros",
    "juniper": "juniper_junos",
    "arista": "arista_eos"
}

def get_device_type(vendor_name):
    return VENDOR_DEVICE_TYPE.get(vendor_name.lower(), None)

def get_verify_command(vendor_name):
    vendor = vendor_name.lower()
    commands = {
        "huawei": "display current-configuration | include local-user",
        "cisco": "show running-config | include username",
        "mikrotik": "/user print",
        "juniper": "show configuration system login user",
        "arista": "show running-config | include username",
    }
    return commands.get(vendor, "show run")

def get_list_users_command(vendor_name):
    vendor = vendor_name.lower()
    commands = {
        "huawei": "display current-configuration | include local-user",
        "cisco": "show running-config | include username",
        "mikrotik": "/user print detail without-paging",
        "juniper": "show configuration system login user",
        "arista": "show running-config | include username",
    }
    return commands.get(vendor, "show run")

def get_create_commands(vendor_name, username, password):
    vendor = vendor_name.lower()
    if vendor == "huawei":
        return [f"local-user {username} password irreversible-cipher {password}",
                f"local-user {username} privilege level 3",
                f"local-user {username} service-type ssh"]
    elif vendor == "cisco" or vendor == "arista":
        return [f"username {username} privilege 15 secret {password}"]
    elif vendor == "mikrotik":
        return [f"/user add name={username} password={password} group=full"]
    elif vendor == "juniper":
        return [f"set system login user {username} class super-user",
                f"set system login user {username} authentication plain-text-password"]
    else:
        return [f"username {username} password {password}"]

def get_delete_commands(vendor_name, username):
    vendor = vendor_name.lower()
    if vendor == "huawei":
        return [f"undo local-user {username}"]
    elif vendor == "cisco" or vendor == "arista":
        return [f"no username {username}"]
    elif vendor == "mikrotik":
        return [f"/user remove {username}"]
    elif vendor == "juniper":
        return [f"delete system login user {username}"]
    else:
        return [f"no username {username}"]

def get_reset_password_commands(vendor_name, username, new_password):
    vendor = vendor_name.lower()
    if vendor == "huawei":
        return [f"local-user {username} password irreversible-cipher {new_password}"]
    elif vendor == "cisco" or vendor == "arista":
        return [f"username {username} secret {new_password}"]
    elif vendor == "mikrotik":
        return [f"/user set {username} password={new_password}"]
    elif vendor == "juniper":
        return [f"set system login user {username} authentication plain-text-password"]
    else:
        return [f"username {username} password {new_password}"]