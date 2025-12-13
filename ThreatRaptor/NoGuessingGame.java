import java.util.Random;
import java.util.Scanner;

public class NoGuessingGame {
    public static void playGame() {
        Scanner sc = new Scanner(System.in);
        Random rand = new Random();
        int score = 0;
        boolean pa = true;//pa = play again
        while (pa) {
            int num = rand.nextInt(100) + 1;
            int noOfAttemptsLeft = 10;
            boolean guessedCorrectly = false;
            System.out.println("\nNew Round! Guess the number between 1 and 100.");
            while (noOfAttemptsLeft > 0) {
                System.out.print("You have " + noOfAttemptsLeft + " attempts left. Enter your guess: ");
                int guess = sc.nextInt();
                if (guess == num) {
                    System.out.println("Your guess is correct, Congratulations!!");
                    score++;
                    guessedCorrectly = true;
                    break;
                } else if (guess < num) {
                    System.out.println("Oh Sorry! The number you guessed is lower than the number you need to guess.");
                } else {
                    System.out.println("Oh Sorry! The number you guessed is higher than the number you need to guess.");
                }
                noOfAttemptsLeft--;
            }
            if (!guessedCorrectly) {
                System.out.println("Sorry, you've run out of attempts. The correct number that you need to guess was " + num + ".");
            }
            System.out.print("Would you like to play another round? (yes/no): ");
            String resp = sc.next().toLowerCase();//resp = response
            pa = resp.equals("yes");
        }
        System.out.println("Game Over! Your score: " + score + " rounds won.");
        sc.close();
    }
    public static void main(String[] args) {
        playGame();
    }
}
