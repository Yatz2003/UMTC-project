
import java.util.*;

public class NumShiftSort {
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

        for (int i = 0; i < numbers.size(); i++) {
            numbers.set(i, numbers.get(i) + shift);
        }

        Collections.sort(numbers);

        System.out.println("Sorted numbers: " + numbers);
        System.out.println("Sum of the first and last number: " + (numbers.getFirst() + numbers.getLast()));
    }
}