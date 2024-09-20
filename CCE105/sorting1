import java.util.*;
public class bubblesort  {
    \\JAMES CHATO LUBEN

    public static void main(String[] args) {
           Scanner scanner = new Scanner(System.in);
   
           System.out.println("Enter Numbers: ");
           String input = scanner.nextLine();
           
           String[] inputArray = input.split(" ");
           int[] arr = new int[inputArray.length];
   
           for (int i = 0; i < inputArray.length; i++) {
               arr[i] = Integer.parseInt(inputArray[i]);
           }
   
           bubbleSortDescending(arr);
   
           System.out.println("\nOutput:");
           boolean showStars = true;
   
           for (int i = 0; i < arr.length; i++) {
               System.out.print(arr[i]);
   
               if (showStars && arr[i] > 0) {
                   System.out.print(" ");
                   for (int j = 0; j < arr[i]; j++) {
                       System.out.print("*");
                   }
               }
   
               showStars = !showStars;
               System.out.println();
           }
       }
   
   
       static void bubbleSortDescending(int[] arr) {
           int n = arr.length;
           for (int i = 0; i < n - 1; i++) {
               for (int j = 0; j < n - i - 1; j++) {
                   if (arr[j] < arr[j + 1]) {

                       int temp = arr[j];
                       arr[j] = arr[j + 1];
                       arr[j + 1] = temp;
                   }
               }
           }
       }
   }
