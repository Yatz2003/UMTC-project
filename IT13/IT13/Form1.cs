using MySql.Data.MySqlClient;
using System;
using System.Data;
using System.Windows.Forms;

namespace IT13
{
    public partial class Form1 : Form
    {
        string connectionString = "server=localhost;database=library_db;user=root;password=;";

        public Form1()
        {
            InitializeComponent();
            Load += Form1_Load; // attach form load event
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            DisplayBooks(); // show books automatically on startup
        }

        private void btnAdd_Click(object sender, EventArgs e)
        {
            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                string query = "INSERT INTO books (isbn, title, author, year, status) VALUES (@isbn, @title, @author, @year, 'Available')";
                MySqlCommand cmd = new MySqlCommand(query, conn);
                cmd.Parameters.AddWithValue("@isbn", txtISBN.Text);
                cmd.Parameters.AddWithValue("@title", txtTitle.Text);
                cmd.Parameters.AddWithValue("@author", txtAuthor.Text);
                cmd.Parameters.AddWithValue("@year", int.Parse(txtYear.Text));

                conn.Open();
                cmd.ExecuteNonQuery();
                conn.Close();

                MessageBox.Show("Book Added Successfully!");
                DisplayBooks();
                ClearFields();
            }
        }

        private void DisplayBooks()
        {
            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                string query = "SELECT id, isbn, title, author, year, status FROM books";
                MySqlDataAdapter adapter = new MySqlDataAdapter(query, conn);
                DataTable dt = new DataTable();
                adapter.Fill(dt);
                dgvBooks.DataSource = dt;
            }
        }

        private void btnSearch_Click(object sender, EventArgs e)
        {
            string keyword = txtTitle.Text; // you can rename this to txtSearch for clarity

            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                string query = "SELECT * FROM books WHERE title LIKE @keyword OR author LIKE @keyword OR isbn LIKE @keyword";
                MySqlCommand cmd = new MySqlCommand(query, conn);
                cmd.Parameters.AddWithValue("@keyword", "%" + keyword + "%");

                MySqlDataAdapter adapter = new MySqlDataAdapter(cmd);
                DataTable dt = new DataTable();
                adapter.Fill(dt);
                dgvBooks.DataSource = dt;
            }
        }

        private void btnUpdate_Click(object sender, EventArgs e)
        {
            if (dgvBooks.CurrentRow != null)
            {
                int id = Convert.ToInt32(dgvBooks.CurrentRow.Cells["id"].Value);

                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    string query = "UPDATE books SET isbn=@isbn, title=@title, author=@author, year=@year WHERE id=@id";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.Parameters.AddWithValue("@isbn", txtISBN.Text);
                    cmd.Parameters.AddWithValue("@title", txtTitle.Text);
                    cmd.Parameters.AddWithValue("@author", txtAuthor.Text);
                    cmd.Parameters.AddWithValue("@year", int.Parse(txtYear.Text));
                    cmd.Parameters.AddWithValue("@id", id);

                    conn.Open();
                    cmd.ExecuteNonQuery();
                    conn.Close();
                }

                MessageBox.Show("Book Updated!");
                DisplayBooks();
                ClearFields();
            }
            else
            {
                MessageBox.Show("Select a book to update!");
            }
        }

        private void btnDelete_Click(object sender, EventArgs e)
        {
            if (dgvBooks.CurrentRow != null)
            {
                int id = Convert.ToInt32(dgvBooks.CurrentRow.Cells["id"].Value);

                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    string query = "DELETE FROM books WHERE id=@id";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.Parameters.AddWithValue("@id", id);

                    conn.Open();
                    cmd.ExecuteNonQuery();
                    conn.Close();
                }

                MessageBox.Show("Book Deleted!");
                DisplayBooks();
            }
            else
            {
                MessageBox.Show("Select a book to delete!");
            }
        }

        private void ClearFields()
        {
            txtISBN.Clear();
            txtTitle.Clear();
            txtAuthor.Clear();
            txtYear.Clear();
        }

        private void btnReserve_Click(object sender, EventArgs e)
        {
            if (dgvBooks.CurrentRow != null)
            {
                int bookId = Convert.ToInt32(dgvBooks.CurrentRow.Cells["id"].Value);
                string title = dgvBooks.CurrentRow.Cells["title"].Value.ToString();
                string status = dgvBooks.CurrentRow.Cells["status"].Value.ToString();

                if (status == "Available")
                {
                    FormReserve reserveForm = new FormReserve(bookId, title);
                    reserveForm.ShowDialog();
                    DisplayBooks();
                }
                else
                {
                    using (MySqlConnection conn = new MySqlConnection(connectionString))
                    {
                        string query = "SELECT b.title, b.isbn, r.student_id, r.student_name, r.course, r.year_level, r.date_borrowed, r.date_return " +
                                       "FROM reservations r JOIN books b ON r.book_id=b.id WHERE b.id=@bookId ORDER BY r.id DESC LIMIT 1";
                        MySqlCommand cmd = new MySqlCommand(query, conn);
                        cmd.Parameters.AddWithValue("@bookId", bookId);

                        conn.Open();
                        MySqlDataReader reader = cmd.ExecuteReader();
                        if (reader.Read())
                        {
                            MessageBox.Show(
                                $"Book: {reader["title"]} (ISBN: {reader["isbn"]})\n" +
                                $"Borrowed by: {reader["student_name"]} ({reader["student_id"]})\n" +
                                $"Course: {reader["course"]}, Year: {reader["year_level"]}\n" +
                                $"Date Borrowed: {reader["date_borrowed"]}\n" +
                                $"Date Return: {reader["date_return"]}",
                                "Book Unavailable"
                            );
                        }
                        conn.Close();
                    }
                }
            }
            else
            {
                MessageBox.Show("Select a book first!");
            }
        }
    }
}
