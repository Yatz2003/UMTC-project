import math

def calculator():
    print("Welcome to the Python Calculator!")
    print("Select an operation:")
    print("1. Addition")
    print("2. Subtraction")
    print("3. Multiplication")
    print("4. Division")
    print("5. Modulus")
    print("6. Exponentiation")
    print("7. Square Root")
    print("8. Sine")
    print("9. Cosine")
    print("10. Tangent")
    print("11. Logarithm (natural)")
    print("12. Logarithm (base 10)")
    print("13. Exit")

    while True:
        choice = int(input("Enter choice (1-13): "))
        
        if choice == 13:
            print("Exiting the calculator.")
            break

        if choice in range(1, 7):
            num1 = float(input("Enter first number: "))
            num2 = float(input("Enter second number: "))
            if choice == 1:
                result = num1 + num2
            elif choice == 2:
                result = num1 - num2
            elif choice == 3:
                result = num1 * num2
            elif choice == 4:
                result = num1 / num2 if num2 != 0 else "Undefined (division by zero)"
            elif choice == 5:
                result = num1 % num2
            elif choice == 6:
                result = num1 ** num2
            print("Result:", result)

        elif choice == 7:
            num = float(input("Enter number: "))
            result = math.sqrt(num)
            print("Result:", result)

        elif choice == 8:
            num = float(input("Enter angle in degrees: "))
            result = math.sin(math.radians(num))
            print("Result:", result)

        elif choice == 9:
            num = float(input("Enter angle in degrees: "))
            result = math.cos(math.radians(num))
            print("Result:", result)

        elif choice == 10:
            num = float(input("Enter angle in degrees: "))
            result = math.tan(math.radians(num))
            print("Result:", result)

        elif choice == 11:
            num = float(input("Enter number: "))
            result = math.log(num)
            print("Result:", result)

        elif choice == 12:
            num = float(input("Enter number: "))
            result = math.log10(num)
            print("Result:", result)
        
        else:
            print("Invalid choice, please try again.")

calculator()
