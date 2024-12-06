import tkinter as tk
from tkinter import messagebox, simpledialog
from tkcalendar import Calendar
from datetime import datetime, timedelta

# Event storage
events = {}

def add_event():
    """Add or edit an event for a selected date."""
    selected_date = calendar.get_date()
    event_description = simpledialog.askstring("Add Event", "Enter event description:")
    if not event_description:
        return

    recurrence = messagebox.askyesnocancel("Recurrence", "Is this a recurring event?")
    if recurrence:  # Recurring event
        recurrence_type = simpledialog.askstring(
            "Recurrence Type", "Enter recurrence type: daily or weekly (default: daily):"
        ) or "daily"
        end_date_str = simpledialog.askstring(
            "End Date", "Enter the end date for recurrence (YYYY-MM-DD):"
        )
        try:
            end_date = datetime.strptime(end_date_str, "%Y-%m-%d").date()
            current_date = datetime.strptime(selected_date, "%m/%d/%y").date()
            delta = timedelta(days=1 if recurrence_type.lower() == "daily" else 7)
            while current_date <= end_date:
                events.setdefault(current_date.strftime("%m/%d/%y"), []).append(event_description)
                current_date += delta
        except ValueError:
            messagebox.showerror("Error", "Invalid end date format.")
            return
    else:  # Single event
        events.setdefault(selected_date, []).append(event_description)

    update_dashboard()
    messagebox.showinfo("Success", "Event added successfully!")

def view_events():
    """View events for the selected date."""
    selected_date = calendar.get_date()
    event_list = events.get(selected_date, [])
    if not event_list:
        messagebox.showinfo("No Events", "No events found for the selected date.")
        return

    event_text = "\n".join(f"- {event}" for event in event_list)
    messagebox.showinfo(f"Events on {selected_date}", event_text)

def delete_event():
    """Delete an event from the selected date."""
    selected_date = calendar.get_date()
    event_list = events.get(selected_date, [])
    if not event_list:
        messagebox.showinfo("No Events", "No events found to delete.")
        return

    event_to_delete = simpledialog.askstring(
        "Delete Event", f"Enter the event description to delete:\n{', '.join(event_list)}"
    )
    if event_to_delete in event_list:
        event_list.remove(event_to_delete)
        if not event_list:
            del events[selected_date]
        update_dashboard()
        messagebox.showinfo("Success", "Event deleted successfully!")
    else:
        messagebox.showerror("Error", "Event not found.")

def update_dashboard():
    """Update the upcoming events dashboard."""
    dashboard.delete(1.0, tk.END)
    dashboard.insert(tk.END, "Upcoming Events:\n")
    today = datetime.now().date()
    for date, event_list in sorted(events.items()):
        event_date = datetime.strptime(date, "%m/%d/%y").date()
        if event_date >= today:
            for event in event_list:
                dashboard.insert(tk.END, f"{date}: {event}\n")

# GUI setup
root = tk.Tk()
root.title("Customizable Calendar with Event Reminder")

# Calendar widget
calendar = Calendar(root, selectmode="day", date_pattern="mm/dd/yy")
calendar.pack(pady=10)

# Buttons
button_frame = tk.Frame(root)
button_frame.pack(pady=10)

add_button = tk.Button(button_frame, text="Add Event", command=add_event, width=15)
add_button.grid(row=0, column=0, padx=5)

view_button = tk.Button(button_frame, text="View Events", command=view_events, width=15)
view_button.grid(row=0, column=1, padx=5)

delete_button = tk.Button(button_frame, text="Delete Event", command=delete_event, width=15)
delete_button.grid(row=0, column=2, padx=5)

# Dashboard
dashboard_label = tk.Label(root, text="Upcoming Events Dashboard", font=("Arial", 12, "bold"))
dashboard_label.pack()

dashboard = tk.Text(root, height=10, width=50)
dashboard.pack(pady=10)

update_dashboard()  # Initialize dashboard

root.mainloop()
