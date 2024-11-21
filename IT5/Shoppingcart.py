cart_input = input("Enter items (Example: item:price,...): ")
cart = {item.split(':')[0].strip(): 
    float(item.split(':')[1].strip()) 
    for item in cart_input.split(',')}
while True:
    print("Options: [1] Display Items [2] Find Item [3] Calculate Total")
    choice = input("Palihog ug Pili: ")
    if choice == '1':
        print("Items in Cart:")
        for item, price in cart.items():
            print(f"{item}: {price:.2f}")
    elif choice == '2': 
        item = input("Enter item name to find: ").strip()
        if item in cart:
            print(f"{item} is {cart[item]:.2f}.")
        else:
            print(f"{item} is not found in the cart.")
    elif choice == '3':
        total = sum(cart.values())
        print(f"Total Cost: {total:.2f}")
    else:
        print("Invalid input, Exiting!.")
        break