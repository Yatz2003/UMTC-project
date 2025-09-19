using System;
using System.Windows.Forms;

namespace IT13
{
    public partial class FormLogin : Form
    {
        public FormLogin()
        {
            InitializeComponent();
        }

        private void btnAdmin_Click(object sender, EventArgs e)
        {
            FormAdminLogin adminLogin = new FormAdminLogin();
            adminLogin.Show();
            this.Hide();
        }

        private void btnStudent_Click(object sender, EventArgs e)
        {
            // TODO: Will add Student Form later based on your instructions
            MessageBox.Show("Student login will be added soon.");
        }
    }
}
