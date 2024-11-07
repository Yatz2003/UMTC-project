number1 = int(input("Enter number 1: "))
number2 = int(input("Enter number 2: "))

sum_result = number1 + number2
difference_result = number1 - number2
quotient_result = number1 / number2 if number2 != 0 else "undefined (division by zero)"
product_result = number1 * number2
modulus_result = number1 % number2 if number2 != 0 else "undefined (division by zero)"
print("The sum is:", sum_result)
print("The difference is:", difference_result)
print("The quotient is:", quotient_result)
print("The product is:", product_result)
print("The modulus is:", modulus_result)
