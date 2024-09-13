import java.util.Scanner;

public class CaesarCipher {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);

        System.out.println("Do you want to (E)ncrypt or (D)ecrypt?");
        String choice = scanner.next().toUpperCase();

        if (choice.equals("E")) {
            System.out.println("Enter the text to encrypt:");
            String plainText = scanner.next();
            scanner.nextLine();=
            System.out.println("Enter the shift value:");
            int shift = scanner.nextInt();

            String encryptedText = encrypt(plainText, shift);
            System.out.println("Encrypted Text: " + encryptedText);
        } else if (choice.equals("D")) {
            System.out.println("Enter the text to decrypt:");
            String encryptedText = scanner.next();
            scanner.nextLine(); 
            System.out.println("Enter the shift value:");
            int shift = scanner.nextInt();

            String decryptedText = decrypt(encryptedText, shift);
            System.out.println("Decrypted Text: " + decryptedText);
        } else {
            System.out.println("Invalid choice. Please enter E to encrypt or D to decrypt.");
        }
    }

    public static String encrypt(String plainText, int shift) {
        StringBuilder encryptedText = new StringBuilder();

        for (char c : plainText.toCharArray()) {
            if (Character.isLetter(c)) {
                char base = Character.isUpperCase(c) ? 'A' : 'a';
                encryptedText.append((char) ((c - base + shift) % 26 + base));
            } else {
                encryptedText.append(c);
            }
        }

        return encryptedText.toString();
    }

    public static String decrypt(String encryptedText, int shift) {
        return encrypt(encryptedText, 26 - shift); 
    }
}
