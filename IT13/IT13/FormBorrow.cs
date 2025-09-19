using System;
using System.Windows.Forms;
using MySql.Data.MySqlClient;

namespace IT13
{
    public partial class FormBorrow : Form
    {
        string connectionString = "server=localhost;database=library_db;uid=root;pwd=;";

        string _isbn, _title, _author, _year;

        public FormBorrow(string isbn, string title, string author, string year)
        {
            InitializeComponent();
            _isbn = isbn;
            _title = title;
            _author = author;
            _year = year;

            lblBookInfo.Text = $"ISBN: {isbn}\nTitle: {title}\nAuthor: {author}\nYear: {year}";
        }

        private void btnConfirmBorrow_Click(object sender, EventArgs e)
        {
            string studentId = txtStudentId.Text.Trim();
            string studentName = txtStudentName.Text.Trim();
            string program = txtProgram.Text.Trim();
            string yearLevel = txtYearLevel.Text.Trim();

            if (string.IsNullOrEmpty(studentId) || string.IsNullOrEmpty(studentName) ||
                string.IsNullOrEmpty(program) || string.IsNullOrEmpty(yearLevel))
            {
                MessageBox.Show("Please fill in all student details.");
                return;
            }

            DateTime borrowDate = DateTime.Now;
            DateTime dueDate = borrowDate.AddDays(3);

            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                try
                {
                    conn.Open();

                    // Insert into borrow_requests as Pending
                    string query = @"INSERT INTO borrow_requests 
                                    (student_id, student_name, program, year_level, isbn, title, borrow_date, due_date, status) 
                                    VALUES (@student_id, @student_name, @program, @year_level, @isbn, @title, @borrow_date, @due_date, 'Pending')";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.Parameters.AddWithValue("@student_id", studentId);
                    cmd.Parameters.AddWithValue("@student_name", studentName);
                    cmd.Parameters.AddWithValue("@program", program);
                    cmd.Parameters.AddWithValue("@year_level", yearLevel);
                    cmd.Parameters.AddWithValue("@isbn", _isbn);
                    cmd.Parameters.AddWithValue("@title", _title);
                    cmd.Parameters.AddWithValue("@borrow_date", borrowDate);
                    cmd.Parameters.AddWithValue("@due_date", dueDate);
                    cmd.ExecuteNonQuery();

                    // Update book status to "For Approval"
                    string query2 = "UPDATE books SET status='For Approval' WHERE isbn=@isbn";
                    MySqlCommand cmd2 = new MySqlCommand(query2, conn);
                    cmd2.Parameters.AddWithValue("@isbn", _isbn);
                    cmd2.ExecuteNonQuery();

                    MessageBox.Show($"Your item is due back on {dueDate:MMMM dd, yyyy}. Please make sure to return it on time to avoid late fees.\n\nPlease proceed to the counter to complete the borrowing process.");
                    this.Close();
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Error borrowing book: " + ex.Message);
                }
            }
        }
    }
}
