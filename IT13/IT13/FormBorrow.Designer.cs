namespace IT13
{
    partial class FormBorrow
    {
        private System.ComponentModel.IContainer components = null;
        private System.Windows.Forms.Label lblBookInfo;
        private System.Windows.Forms.TextBox txtStudentId;
        private System.Windows.Forms.TextBox txtStudentName;
        private System.Windows.Forms.TextBox txtProgram;
        private System.Windows.Forms.TextBox txtYearLevel;
        private System.Windows.Forms.Button btnConfirmBorrow;
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
            this.lblBookInfo = new System.Windows.Forms.Label();
            this.txtStudentId = new System.Windows.Forms.TextBox();
            this.txtStudentName = new System.Windows.Forms.TextBox();
            this.txtProgram = new System.Windows.Forms.TextBox();
            this.txtYearLevel = new System.Windows.Forms.TextBox();
            this.btnConfirmBorrow = new System.Windows.Forms.Button();
            this.lblStudentId = new System.Windows.Forms.Label();
            this.lblStudentName = new System.Windows.Forms.Label();
            this.lblProgram = new System.Windows.Forms.Label();
            this.lblYearLevel = new System.Windows.Forms.Label();
            this.SuspendLayout();

            // lblBookInfo
            this.lblBookInfo.AutoSize = true;
            this.lblBookInfo.Location = new System.Drawing.Point(30, 20);
            this.lblBookInfo.Size = new System.Drawing.Size(300, 80);
            this.lblBookInfo.Text = "Book Info will appear here";

            // Student ID
            this.lblStudentId.Location = new System.Drawing.Point(30, 120);
            this.lblStudentId.Text = "Student ID:";
            this.txtStudentId.Location = new System.Drawing.Point(150, 120);
            this.txtStudentId.Size = new System.Drawing.Size(200, 27);

            // Student Name
            this.lblStudentName.Location = new System.Drawing.Point(30, 160);
            this.lblStudentName.Text = "Name:";
            this.txtStudentName.Location = new System.Drawing.Point(150, 160);
            this.txtStudentName.Size = new System.Drawing.Size(200, 27);

            // Program
            this.lblProgram.Location = new System.Drawing.Point(30, 200);
            this.lblProgram.Text = "Program:";
            this.txtProgram.Location = new System.Drawing.Point(150, 200);
            this.txtProgram.Size = new System.Drawing.Size(200, 27);

            // Year Level
            this.lblYearLevel.Location = new System.Drawing.Point(30, 240);
            this.lblYearLevel.Text = "Year Level:";
            this.txtYearLevel.Location = new System.Drawing.Point(150, 240);
            this.txtYearLevel.Size = new System.Drawing.Size(200, 27);

            // Confirm Borrow
            this.btnConfirmBorrow.Location = new System.Drawing.Point(30, 290);
            this.btnConfirmBorrow.Size = new System.Drawing.Size(150, 35);
            this.btnConfirmBorrow.Text = "Confirm Borrow";
            this.btnConfirmBorrow.Click += new System.EventHandler(this.btnConfirmBorrow_Click);

            // FormBorrow
            this.ClientSize = new System.Drawing.Size(400, 350);
            this.Controls.Add(this.lblBookInfo);
            this.Controls.Add(this.txtStudentId);
            this.Controls.Add(this.txtStudentName);
            this.Controls.Add(this.txtProgram);
            this.Controls.Add(this.txtYearLevel);
            this.Controls.Add(this.btnConfirmBorrow);
            this.Controls.Add(this.lblStudentId);
            this.Controls.Add(this.lblStudentName);
            this.Controls.Add(this.lblProgram);
            this.Controls.Add(this.lblYearLevel);
            this.Text = "Borrow Book";

            this.ResumeLayout(false);
            this.PerformLayout();
        }
    }
}
