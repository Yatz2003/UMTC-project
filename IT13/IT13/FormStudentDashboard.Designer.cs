namespace IT13
{
    partial class FormStudentDashboard
    {
        private System.ComponentModel.IContainer components = null;
        private System.Windows.Forms.DataGridView dgvAvailableBooks;
        private System.Windows.Forms.TextBox txtSearch;
        private System.Windows.Forms.Button btnSearch;
        private System.Windows.Forms.Button btnBorrow;
        private System.Windows.Forms.TextBox txtStudentId;
        private System.Windows.Forms.TextBox txtStudentName;
        private System.Windows.Forms.TextBox txtProgram;
        private System.Windows.Forms.TextBox txtYearLevel;
        private System.Windows.Forms.Label lblStudentId;
        private System.Windows.Forms.Label lblStudentName;
        private System.Windows.Forms.Label lblProgram;
        private System.Windows.Forms.Label lblYearLevel;

        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null)) { components.Dispose(); }
            base.Dispose(disposing);
        }

        private void InitializeComponent()
        {
            this.dgvAvailableBooks = new System.Windows.Forms.DataGridView();
            this.txtSearch = new System.Windows.Forms.TextBox();
            this.btnSearch = new System.Windows.Forms.Button();
            this.btnBorrow = new System.Windows.Forms.Button();
            this.txtStudentId = new System.Windows.Forms.TextBox();
            this.txtStudentName = new System.Windows.Forms.TextBox();
            this.txtProgram = new System.Windows.Forms.TextBox();
            this.txtYearLevel = new System.Windows.Forms.TextBox();
            this.lblStudentId = new System.Windows.Forms.Label();
            this.lblStudentName = new System.Windows.Forms.Label();
            this.lblProgram = new System.Windows.Forms.Label();
            this.lblYearLevel = new System.Windows.Forms.Label();

            ((System.ComponentModel.ISupportInitialize)(this.dgvAvailableBooks)).BeginInit();
            this.SuspendLayout();

            // dgvAvailableBooks
            this.dgvAvailableBooks.ColumnHeadersHeightSizeMode = System.Windows.Forms.DataGridViewColumnHeadersHeightSizeMode.AutoSize;
            this.dgvAvailableBooks.Location = new System.Drawing.Point(30, 80);
            this.dgvAvailableBooks.Name = "dgvAvailableBooks";
            this.dgvAvailableBooks.RowHeadersWidth = 51;
            this.dgvAvailableBooks.Size = new System.Drawing.Size(720, 250);
            this.dgvAvailableBooks.TabIndex = 0;

            // txtSearch
            this.txtSearch.Location = new System.Drawing.Point(30, 30);
            this.txtSearch.Size = new System.Drawing.Size(200, 27);

            // btnSearch
            this.btnSearch.Location = new System.Drawing.Point(240, 30);
            this.btnSearch.Size = new System.Drawing.Size(80, 27);
            this.btnSearch.Text = "Search";
            this.btnSearch.Click += new System.EventHandler(this.btnSearch_Click);

            // btnBorrow
            this.btnBorrow.Location = new System.Drawing.Point(30, 450);
            this.btnBorrow.Size = new System.Drawing.Size(120, 35);
            this.btnBorrow.Text = "Borrow";
            this.btnBorrow.Click += new System.EventHandler(this.btnBorrow_Click);

            // Student Info
            this.lblStudentId.Location = new System.Drawing.Point(30, 350);
            this.lblStudentId.Text = "Student ID:";
            this.txtStudentId.Location = new System.Drawing.Point(130, 350);
            this.txtStudentId.Size = new System.Drawing.Size(200, 27);

            this.lblStudentName.Location = new System.Drawing.Point(350, 350);
            this.lblStudentName.Text = "Name:";
            this.txtStudentName.Location = new System.Drawing.Point(420, 350);
            this.txtStudentName.Size = new System.Drawing.Size(200, 27);

            this.lblProgram.Location = new System.Drawing.Point(30, 390);
            this.lblProgram.Text = "Program:";
            this.txtProgram.Location = new System.Drawing.Point(130, 390);
            this.txtProgram.Size = new System.Drawing.Size(200, 27);

            this.lblYearLevel.Location = new System.Drawing.Point(350, 390);
            this.lblYearLevel.Text = "Year Level:";
            this.txtYearLevel.Location = new System.Drawing.Point(420, 390);
            this.txtYearLevel.Size = new System.Drawing.Size(200, 27);

            // FormStudentDashboard
            this.ClientSize = new System.Drawing.Size(800, 520);
            this.Controls.Add(this.dgvAvailableBooks);
            this.Controls.Add(this.txtSearch);
            this.Controls.Add(this.btnSearch);
            this.Controls.Add(this.btnBorrow);
            this.Controls.Add(this.txtStudentId);
            this.Controls.Add(this.txtStudentName);
            this.Controls.Add(this.txtProgram);
            this.Controls.Add(this.txtYearLevel);
            this.Controls.Add(this.lblStudentId);
            this.Controls.Add(this.lblStudentName);
            this.Controls.Add(this.lblProgram);
            this.Controls.Add(this.lblYearLevel);
            this.Text = "Student Dashboard";

            ((System.ComponentModel.ISupportInitialize)(this.dgvAvailableBooks)).EndInit();
            this.ResumeLayout(false);
            this.PerformLayout();
        }
    }
}
