import java.util.*;

public class FinalLabEx3 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Enter a list of numbers separated by spaces: ");
        String[] input = sc.nextLine().split(" ");
        LinkedList<Integer> numbers = new LinkedList<>();
        for (String s : input) {
            numbers.add(Integer.parseInt(s));
        }
        System.out.print("Enter shift key value: ");
        int shift = sc.nextInt();

        LinkedList<Integer> encryptedNumbers = new LinkedList<>();
        for (int num : numbers) {
            encryptedNumbers.add(num + shift);
        }     

        System.out.println("Encrypted numbers: " + encryptedNumbers);

        Collections.sort(encryptedNumbers);
        
        System.out.println("Sorted numbers: " + encryptedNumbers);
        System.out.println("Sum of the first and last number: " + (encryptedNumbers.getFirst() + encryptedNumbers.getLast()));
    }
}