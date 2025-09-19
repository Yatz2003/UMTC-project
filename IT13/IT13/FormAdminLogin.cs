using System;
using System.Windows.Forms;

namespace IT13
{
    public partial class FormAdminLogin : Form
    {
        public FormAdminLogin()
        {
            InitializeComponent();
        }

        private void btnLogin_Click(object sender, EventArgs e)
        {
            string username = txtUsername.Text;
            string password = txtPassword.Text;

            if (username == "admin" && password == "123")
            {
                MessageBox.Show("Login successful!");
                FormAdminDashboard dashboard = new FormAdminDashboard();
                dashboard.Show();
                this.Hide();
            }
            else
            {
                MessageBox.Show("Wrong username or password. Please try again.");
                txtUsername.Clear();
                txtPassword.Clear();
            }
        }
    }
}
