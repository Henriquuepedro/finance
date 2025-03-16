
# Finance Management System

This PHP-based system helps manage personal or business finances. It offers real-time tracking of available funds, expenses, income, and the ability to visualize financial trends through interactive charts. The system divides expenses into fixed and variable categories and helps set goals for monthly spending.

## Features

### 1. **Dashboard**
- View the current available balance for use.
- Track **expenses** and **income** in real-time.
- Visualize **monthly spending** through interactive charts.
- Analyze **fixed expenses** such as rent, utilities (water, electricity), and internet.
- Track **variable expenses** such as leisure activities, grocery shopping, and more.

### 2. **Expense Categories**
- **Fixed Expenses**: Manage recurring expenses that don't change from month to month (e.g., rent, utility bills).
- **Variable Expenses**: Track fluctuating costs that change each month (e.g., groceries, entertainment).
  
### 3. **Financial Goal Setting**
- Set spending goals for both fixed and variable expenses.
- Monitor progress towards reaching your financial goals.
  
### 4. **Charts and Visualization**
- Use charts to visualize monthly expenses, helping you track spending patterns.
- Easily compare fixed and variable expenses.

### 5. **Real-time Tracking**
- Track and update financial data in real-time, providing a clear picture of your financial situation at any moment.

## Installation

Follow these steps to set up the project locally:

1. Clone the repository:
   ```
   git clone https://github.com/Henriquuepedro/finance.git
   ```

2. Navigate to the project directory:
   ```
   cd finance
   ```

3. Install dependencies using Composer:
   ```
   composer install
   ```

4. Set up the environment variables. Copy the `.env.example` file to `.env` and configure the necessary details (e.g., database, SMTP settings):
   ```
   cp .env.example .env
   ```

5. Generate the application key:
   ```
   php artisan key:generate
   ```

6. Run migrations to set up the database:
   ```
   php artisan migrate
   ```

7. Run the application:
   ```
   php artisan serve
   ```

The application should now be running at `http://localhost:8000`.

## Contributing

Feel free to fork this project and submit pull requests. If you encounter any bugs or have suggestions for improvements, please open an issue on GitHub.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
