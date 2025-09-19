using MySql.Data.MySqlClient;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Xml.Linq;

namespace IT13
{
    public partial class FormReserve : Form
    {
        int bookId;
        string connectionString = "server=localhost;database=library_db;user=root;password=;";

        public FormReserve(int id, string title)
        {
            InitializeComponent();
            bookId = id;
            lblBookTitle.Text = "Reserving: " + title;
        }

        private void btnSave_Click(object sender, EventArgs e)
        {
            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                string query = "INSERT INTO reservations (book_id, student_id, student_name, course, year_level, date_borrowed, date_return) " +
                               "VALUES (@book_id, @student_id, @student_name, @course, @year_level, @date_borrowed, @date_return)";
                MySqlCommand cmd = new MySqlCommand(query, conn);
                cmd.Parameters.AddWithValue("@book_id", bookId);
                cmd.Parameters.AddWithValue("@student_id", txtStudentID.Text);
                cmd.Parameters.AddWithValue("@student_name", txtName.Text);
                cmd.Parameters.AddWithValue("@course", txtCourse.Text);
                cmd.Parameters.AddWithValue("@year_level", txtYearLevel.Text);
                cmd.Parameters.AddWithValue("@date_borrowed", dtBorrowed.Value);
                cmd.Parameters.AddWithValue("@date_return", dtReturn.Value);

                conn.Open();
                cmd.ExecuteNonQuery();

                // Update book status to Unavailable
                string updateBook = "UPDATE books SET status='Unavailable' WHERE id=@id";
                MySqlCommand cmd2 = new MySqlCommand(updateBook, conn);
                cmd2.Parameters.AddWithValue("@id", bookId);
                cmd2.ExecuteNonQuery();

                conn.Close();
            }

            MessageBox.Show("Book reserved successfully!");
            this.Close();
        }
    }
}