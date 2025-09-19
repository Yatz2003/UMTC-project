using System;
using System.Data;
using System.Windows.Forms;
using MySql.Data.MySqlClient;

namespace IT13
{
    public partial class FormAdminDashboard : Form
    {
        string connectionString = "server=localhost;database=library_db;uid=root;pwd=;";

        public FormAdminDashboard()
        {
            InitializeComponent();
            AutoUpdateUnreturned();
            LoadBooks(" ORDER BY title ASC");
        }

        // Load books with filter and order
        private void LoadBooks(string orderBy = "", string filter = "")
        {
            try
            {
                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    conn.Open();
                    string query = "SELECT id, isbn, title, author, year, status FROM books";

                    if (!string.IsNullOrEmpty(filter) && filter != "All")
                        query += " WHERE status = @filter";

                    query += orderBy;

                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    if (!string.IsNullOrEmpty(filter) && filter != "All")
                        cmd.Parameters.AddWithValue("@filter", filter);

                    MySqlDataAdapter da = new MySqlDataAdapter(cmd);
                    DataTable dt = new DataTable();
                    da.Fill(dt);
                    dgvBooks.DataSource = dt;
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Error loading books: " + ex.Message);
            }
        }

        private void LoadPendingRequests()
        {
            try
            {
                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    conn.Open();
                    string query = "SELECT id, student_id, student_name, program, year_level, isbn, title, status " +
                                   "FROM borrow_requests WHERE status = 'Pending'";
                    MySqlDataAdapter da = new MySqlDataAdapter(query, conn);
                    DataTable dt = new DataTable();
                    da.Fill(dt);
                    dgvBooks.DataSource = dt;
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Error loading requests: " + ex.Message);
            }
        }

        private void AutoUpdateUnreturned()
        {
            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                try
                {
                    conn.Open();
                    string query = @"UPDATE books b
                                     JOIN borrow_requests r ON b.isbn = r.isbn
                                     SET b.status = 'Unreturned', r.status = 'Unreturned'
                                     WHERE r.status = 'Approved' AND r.due_date < NOW()";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.ExecuteNonQuery();
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Error updating overdue books: " + ex.Message);
                }
            }
        }

        private void ApproveRequest(int requestId, string isbn)
        {
            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                try
                {
                    conn.Open();

                    string query1 = "UPDATE borrow_requests SET status='Approved', borrow_date=NOW(), due_date=DATE_ADD(NOW(), INTERVAL 3 DAY) WHERE id=@id";
                    MySqlCommand cmd1 = new MySqlCommand(query1, conn);
                    cmd1.Parameters.AddWithValue("@id", requestId);
                    cmd1.ExecuteNonQuery();

                    string query2 = "UPDATE books SET status='Borrowed' WHERE isbn=@isbn";
                    MySqlCommand cmd2 = new MySqlCommand(query2, conn);
                    cmd2.Parameters.AddWithValue("@isbn", isbn);
                    cmd2.ExecuteNonQuery();

                    MessageBox.Show("Request Approved! Book marked as Borrowed.");
                    LoadPendingRequests();
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Error approving request: " + ex.Message);
                }
            }
        }

        private void DeclineRequest(int requestId)
        {
            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                try
                {
                    conn.Open();
                    string query = "UPDATE borrow_requests SET status='Declined' WHERE id=@id";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.Parameters.AddWithValue("@id", requestId);
                    cmd.ExecuteNonQuery();

                    MessageBox.Show("Request Declined.");
                    LoadPendingRequests();
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Error declining request: " + ex.Message);
                }
            }
        }

        private void btnViewBooks_Click(object sender, EventArgs e)
        {
            AutoUpdateUnreturned();
            LoadBooks(" ORDER BY title ASC");
        }

        private void btnForApproval_Click(object sender, EventArgs e)
        {
            LoadPendingRequests();
        }

        private void cmbSortBy_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (cmbSortBy.SelectedItem != null)
            {
                string orderBy = "";
                switch (cmbSortBy.SelectedItem.ToString())
                {
                    case "Title ASC": orderBy = " ORDER BY title ASC"; break;
                    case "Title DESC": orderBy = " ORDER BY title DESC"; break;
                    case "Year ASC": orderBy = " ORDER BY year ASC"; break;
                    case "Year DESC": orderBy = " ORDER BY year DESC"; break;
                }
                LoadBooks(orderBy, cmbFilterBy.SelectedItem?.ToString());
            }
        }

        private void cmbFilterBy_SelectedIndexChanged(object sender, EventArgs e)
        {
            LoadBooks("", cmbFilterBy.SelectedItem.ToString());
        }

        private void btnAdd_Click(object sender, EventArgs e)
        {
            using (MySqlConnection conn = new MySqlConnection(connectionString))
            {
                try
                {
                    conn.Open();
                    string query = "INSERT INTO books (isbn, title, author, year, status) VALUES (@isbn,@title,@author,@year,'Available')";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.Parameters.AddWithValue("@isbn", txtISBN.Text);
                    cmd.Parameters.AddWithValue("@title", txtTitle.Text);
                    cmd.Parameters.AddWithValue("@author", txtAuthor.Text);
                    cmd.Parameters.AddWithValue("@year", txtYear.Text);
                    cmd.ExecuteNonQuery();

                    MessageBox.Show("Book Added!");
                    LoadBooks();
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Error adding: " + ex.Message);
                }
            }
        }

        private void btnUpdate_Click(object sender, EventArgs e)
        {
            if (dgvBooks.SelectedRows.Count > 0)
            {
                int id = Convert.ToInt32(dgvBooks.SelectedRows[0].Cells["id"].Value);

                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    try
                    {
                        conn.Open();
                        string query = "UPDATE books SET isbn=@isbn, title=@title, author=@author, year=@year WHERE id=@id";
                        MySqlCommand cmd = new MySqlCommand(query, conn);
                        cmd.Parameters.AddWithValue("@isbn", txtISBN.Text);
                        cmd.Parameters.AddWithValue("@title", txtTitle.Text);
                        cmd.Parameters.AddWithValue("@author", txtAuthor.Text);
                        cmd.Parameters.AddWithValue("@year", txtYear.Text);
                        cmd.Parameters.AddWithValue("@id", id);
                        cmd.ExecuteNonQuery();

                        MessageBox.Show("Book Updated!");
                        LoadBooks();
                    }
                    catch (Exception ex)
                    {
                        MessageBox.Show("Error updating: " + ex.Message);
                    }
                }
            }
        }

        private void btnDelete_Click(object sender, EventArgs e)
        {
            if (dgvBooks.SelectedRows.Count > 0)
            {
                int id = Convert.ToInt32(dgvBooks.SelectedRows[0].Cells["id"].Value);

                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    try
                    {
                        conn.Open();
                        string query = "DELETE FROM books WHERE id=@id";
                        MySqlCommand cmd = new MySqlCommand(query, conn);
                        cmd.Parameters.AddWithValue("@id", id);
                        cmd.ExecuteNonQuery();

                        MessageBox.Show("Book Deleted!");
                        LoadBooks();
                    }
                    catch (Exception ex)
                    {
                        MessageBox.Show("Error deleting: " + ex.Message);
                    }
                }
            }
        }

        private void btnSearch_Click(object sender, EventArgs e)
        {
            try
            {
                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    conn.Open();
                    string query = "SELECT id, isbn, title, author, year, status FROM books WHERE title LIKE @search OR author LIKE @search";
                    MySqlCommand cmd = new MySqlCommand(query, conn);
                    cmd.Parameters.AddWithValue("@search", "%" + txtSearch.Text + "%");
                    MySqlDataAdapter da = new MySqlDataAdapter(cmd);
                    DataTable dt = new DataTable();
                    da.Fill(dt);
                    dgvBooks.DataSource = dt;
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Error searching: " + ex.Message);
            }
        }

        private void contextMenu_Approve_Click(object sender, EventArgs e)
        {
            if (dgvBooks.SelectedRows.Count > 0)
            {
                int requestId = Convert.ToInt32(dgvBooks.SelectedRows[0].Cells["id"].Value);
                string isbn = dgvBooks.SelectedRows[0].Cells["isbn"].Value.ToString();
                ApproveRequest(requestId, isbn);
            }
        }

        private void contextMenu_Decline_Click(object sender, EventArgs e)
        {
            if (dgvBooks.SelectedRows.Count > 0)
            {
                int requestId = Convert.ToInt32(dgvBooks.SelectedRows[0].Cells["id"].Value);
                DeclineRequest(requestId);
            }
        }

        private void dgvBooks_CellContentClick(object sender, DataGridViewCellEventArgs e)
        {

        }
    }
}
