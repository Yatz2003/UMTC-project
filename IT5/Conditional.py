import random

# Generate a random number between 1 and 10
correct_number = random.randint(1, 10)
attempts = 0

print("Welcome to the Number Guessing Game!")
print("Guess a number between 1 and 10. You have 3 attempts.")

while attempts < 3:
    # Prompt the user for their guess
    guess = int(input("Enter your guess: "))
    attempts += 1

    # Check the user's guess
    if guess == correct_number:
        print("Congratulations! You guessed the correct number.")
        break
    elif guess < correct_number:
        print("Too low! Try again.")
    else:
        print("Too high! Try again.")

if attempts == 3 and guess != correct_number:
    print(f"Sorry, you've used all your attempts. The correct number was {correct_number}.")
