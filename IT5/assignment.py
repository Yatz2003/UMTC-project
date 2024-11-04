grade = int(input("Enter your grade (0-100): "))

if 96 <= grade <= 100:
    conversion, letter_grade, description = 4.0, "A", "High Distinction"
elif 90 <= grade <= 95:
    conversion, letter_grade, description = 3.5, "B+", "Distinction"
elif 85 <= grade <= 89:
    conversion, letter_grade, description = 3.0, "B-", "Very Good"
elif 80 <= grade <= 84:
    conversion, letter_grade, description = 2.5, "C+", "Good"
elif 75 <= grade <= 79:
    conversion, letter_grade, description = 2.0, "C-", "Average"
elif grade < 75:
    conversion, letter_grade, description = 1.0, "F", "Fail"
else:
    print("Invalid grade entered.")
    exit()

print(f"Conversion: {conversion}")
print(f"Grade: {letter_grade}")
print(f"Description: {description}")
