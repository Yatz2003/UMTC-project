package encryption;

import java.util.Scanner;

public class Encryption {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);

        System.out.print("Input word: ");
        String word = scanner.nextLine();

        if (word.length() == 6) {
            String transformedWord = transformWord(word);
            System.out.println("Transformed word: " + transformedWord);
        } else {
            String encryptedWord = encryptWord(word);
            String trimmedWord = trimWord(encryptedWord);
            System.out.println("Encrypted text: " + encryptedWord);
            System.out.println("Trimmed text: " + trimmedWord);
        }
    }

    public static String transformWord(String word) {
        String firstChar = word.substring(0, 1).toUpperCase();
        String lastChar = word.substring(word.length() - 1, word.length()).toUpperCase();
        String middleChars = word.substring(1, word.length() - 1).toLowerCase();
        return firstChar + middleChars + lastChar;
    }

    public static String encryptWord(String word) {
        StringBuilder encryptedWord = new StringBuilder();
        for (char c : word.toLowerCase().toCharArray()) {
            if (c == 'a') {
                encryptedWord.append('z');
            } else if (c == ' ') {
                encryptedWord.append(' ');
            } else {
                encryptedWord.append((char) (c - 1));
            }
        }
        return encryptedWord.toString();
    }

    public static String trimWord(String word) {
        return word.substring(1, word.length() - 1);
    }
}
