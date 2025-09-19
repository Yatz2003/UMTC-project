using System;
using System.Data;
using System.Windows.Forms;
using MySql.Data.MySqlClient;

namespace IT13
{
    public partial class FormStudentDashboard : Form
    {
        string connectionString = "server=localhost;database=library_db;uid=root;pwd=;";

        public FormStudentDashboard()
        {
            InitializeComponent();
            LoadBooks();
        }

        // Load available books (not borrowed/for approval/unreturned)
        private void LoadBooks(string search = "")
        {
            try
            {
                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    conn.Open();
                    string query = "SELECT isbn, title, author, year, status FROM books WHERE status = 'Available'";

                    if (!string.IsNullOrEmpty(search))
                    {
                        query += " AND (title LIKE @search OR author LIKE @search OR isbn LIKE @search)";
                    }

                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    if (!string.IsNullOrEmpty(search))
                        cmd.Parameters.AddWithValue("@search", "%" + search + "%");

                    MySqlDataAdapter da = new MySqlDataAdapter(cmd);
                    DataTable dt = new DataTable();
                    da.Fill(dt);
                    dgvAvailableBooks.DataSource = dt;
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Error loading books: " + ex.Message);
            }
        }

        // Search button
        private void btnSearch_Click(object sender, EventArgs e)
        {
            LoadBooks(txtSearch.Text.Trim());
        }

        // Borrow button
        private void btnBorrow_Click(object sender, EventArgs e)
        {
            if (dgvAvailableBooks.SelectedRows.Count == 0)
            {
                MessageBox.Show("Please select a book to borrow.");
                return;
            }

            string isbn = dgvAvailableBooks.SelectedRows[0].Cells["isbn"].Value.ToString();
            string title = dgvAvailableBooks.SelectedRows[0].Cells["title"].Value.ToString();

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

            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                try
                {
                    conn.Open();

                    // Insert into borrow_requests
                    string query = @"INSERT INTO borrow_requests 
                                    (student_id, student_name, program, year_level, isbn, title, status) 
                                    VALUES (@student_id, @student_name, @program, @year_level, @isbn, @title, 'Pending')";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.Parameters.AddWithValue("@student_id", studentId);
                    cmd.Parameters.AddWithValue("@student_name", studentName);
                    cmd.Parameters.AddWithValue("@program", program);
                    cmd.Parameters.AddWithValue("@year_level", yearLevel);
                    cmd.Parameters.AddWithValue("@isbn", isbn);
                    cmd.Parameters.AddWithValue("@title", title);
                    cmd.ExecuteNonQuery();

                    // Update book status to "For Approval"
                    string query2 = "UPDATE books SET status='For Approval' WHERE isbn=@isbn";
                    MySqlCommand cmd2 = new MySqlCommand(query2, conn);
                    cmd2.Parameters.AddWithValue("@isbn", isbn);
                    cmd2.ExecuteNonQuery();

                    MessageBox.Show("Borrow request submitted! Please wait for admin approval.");
                    LoadBooks();
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Error borrowing book: " + ex.Message);
                }
            }
        }
    }
}
