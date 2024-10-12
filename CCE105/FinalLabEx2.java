import java.util.Scanner;

public class FinalLabEx2 {

    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        System.out.print("Enter a word: ");
        String word = scanner.nextLine();

        int charCount = word.length();
        System.out.println("Total characters: " + charCount);

        if (charCount % 2 != 0) {
            String encryptedWord = caesarCipher(word, 3);
            System.out.println("Encrypted word: " + encryptedWord);
        } else {
            String reversedWord = new StringBuilder(word).reverse().toString();
            System.out.println("Reversed word: " + reversedWord);
        }

        int vowelCount = countVowels(word);
        System.out.println("Number of vowels: " + vowelCount);
    }

    public static String caesarCipher(String text, int shift) {
        StringBuilder result = new StringBuilder();
        for (char charac : text.toCharArray()) {
            if (Character.isLetter(charac)) {
                boolean isUpper = Character.isUpperCase(charac);
                char base = isUpper ? 'A' : 'a';
                char shiftedChar = (char) (((charac - base + shift) % 26) + base);
                result.append(shiftedChar);
            } else {
                result.append(charac);
            }
        }
        return result.toString();
    }

    public static int countVowels(String text) {
        int count = 0;
        for (char charac : text.toCharArray()) {
            if ("aeiouAEIOU".indexOf(charac) != -1) {
                count++;
            }
        }
        return count;
    }
}