def networks(phone_num):
    if len(phone_num) == 11 and phone_num[:2] == "09":
        firstfour = phone_num[2:4]
        ikatulo_ug_upat = {
            "Smart": ["13", "14", "20", "21", "28", "29", "30"],
            "TNT": ["08", "09", "10", "11", "12", "18", "19"],
            "Sun": ["22", "23", "32", "33"],
            "Globe": ["15", "16", "17", "25", "26", "27"],
            "TM": ["03", "04", "05", "06", "07"],
            "Red": ["01", "02", "24"]
        }

        for network, digits in ikatulo_ug_upat.items():
            if firstfour in digits:
                return f"This number is {network} network."
        return "Unknown network."
    else:
        return "Invalid phone number."
phone_num = input("Please Enter an 11-digit phone number: ")
print(networks(phone_num))
