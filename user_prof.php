<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Profile & Ticketing Dashboard</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      /* --- Teal Theme Variables --- */
      :root{
        --bg: #00a8a8;          /* Deep Teal Background */
        --card: #fff;           /* White Card */
        --text: #333333;
        --muted: #6b7280;       /* Muted gray for email/details */
        --accent: #007bff;      /* Bright Blue for links/buttons */
        --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        --radius: 12px;
      }

      /* --- Global Styles --- */
      *{box-sizing:border-box;margin:0;padding:0;font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}
      body{
        background:var(--bg);
        color:var(--text);
        min-height:100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
      }

       body::-webkit-scrollbar {
    display: none; /* Hides the scrollbar */
    width: 0; /* Ensures no width space is reserved for the scrollbar */
    }
      
      /* --- Main App Container --- */
      .app-container {
        display: flex;
        max-width: 10000px;
        width: 100%;
        gap: 20px;
        min-height: 500px;
      }

      /* --- Navigation Card (Left) --- */
      .nav-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        width: 300px;
        padding: 20px 0;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* Space out header, menu, and logout */
      }
      .user-header {
        display: flex;
        align-items: center;
        padding: 0 20px 20px;
        border-bottom: 1px solid #eee;
        margin-bottom: 10px;
      }
      .avatar-sm {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin-right: 15px;
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
      }
      .avatar-sm i {
          color: #ccc;
      }
      .user-info .name {
        font-weight: 600;
        font-size: 16px;
      }
      .user-info .email {
        color: var(--muted);
        font-size: 14px;
      }

      /* --- Navigation Links --- */
      .nav-list {
        list-style: none;
        padding-bottom: 10px;
        flex-grow: 1; /* Allows the list to take up available space */
      }
      .nav-list-bottom {
        list-style: none;
        border-top: 1px solid #eee;
        padding-top: 10px;
      }
      .nav-item button {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 12px 20px;
        text-align: left;
        background: none;
        border: none;
        color: var(--text);
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        justify-content: space-between;
      }
      .nav-item button:hover, .nav-item button.active {
        background-color: #f0f8ff; /* Light blue hover */
        color: var(--accent);
      }
      .nav-item button i {
        margin-right: 12px;
        min-width: 20px;
        text-align: center;
      }
      .nav-item button.active i {
        color: var(--accent);
      }
      .nav-item .arrow {
        color: var(--muted);
        font-size: 12px;
      }

      /* --- Content Card (Right) --- */
      .content-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        flex-grow: 1;
        padding: 30px;
        position: relative;
        overflow-y: auto; 
      }
      .content-card h2 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        border-bottom: 2px solid #eee;
        padding-bottom: 5px;
      }
      .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 24px;
        color: var(--muted);
        cursor: pointer;
      }

      /* --- Profile Detail Styles --- */
      .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
      }
      .label {
        font-weight: 500;
        color: var(--muted);
        width: 40%;
      }
      .value {
        text-align: right;
        font-weight: 600;
        width: 60%;
        display: flex;
        align-items: center;
        justify-content: flex-end;
      }
      .edit-icon {
        color: var(--accent);
        margin-left: 10px;
        cursor: pointer;
      }
      input[type="text"] {
        border: none;
        text-align: right;
        padding: 5px;
        font-weight: 600;
        color: var(--text);
        width: 100%;
        background: transparent;
      }
      
      .save-btn {
        background: var(--accent);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        margin-top: 10px;
        cursor: pointer;
        transition: background-color 0.2s;
        float: right;
      }
      .save-btn:hover {
        background: #0056b3;
      }

      /* --- Ticket History Styles --- */
      .ticket-history-container {
        margin-top: 30px;
      }
      .ticket-list {
        list-style: none;
        padding: 0;
      }
      .ticket-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        transition: background-color 0.2s;
        cursor: pointer;
      }
      .ticket-item:hover {
        background-color: #f7f7f7;
      }
      .ticket-details {
        flex-grow: 1;
      }
      .ticket-title {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 2px;
      }
      .ticket-date {
        font-size: 12px;
        color: var(--muted);
      }
      .ticket-status {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
        color: white;
      }
      .status-pending { background-color: #ffc107; color: #333; }
      .status-resolved { background-color: #28a745; }
      .status-open { background-color: var(--accent); }

      /* --- Responsive adjustments --- */
      @media (max-width: 768px) {
        .app-container {
          flex-direction: column;
          align-items: center;
          gap: 15px;
        }
        .nav-card {
          width: 95vw;
          max-width: 450px;
        }
        .content-card {
          width: 95vw;
          max-width: 450px;
          padding: 20px;
        }
        .detail-row {
          flex-direction: column;
          align-items: flex-start;
          padding: 10px 0;
        }
        .label {
          width: 100%;
          margin-bottom: 5px;
          color: var(--text);
        }
        .value {
          width: 100%;
          text-align: left;
          justify-content: flex-start;
        }
        input[type="text"] {
            text-align: left;
        }
        .edit-icon {
          display: none; 
        }
      }
    </style>
  </head>
  <body>

    <div class="app-container">
        <!-- Navigation Card (Left Panel) -->
        <div class="nav-card">
            <div>
                <!-- User Header -->
                <div class="user-header">
                    <div class="avatar-sm">
                        <i class="fa-solid fa-circle-user fa-2x"></i>
                    </div>
                    <div class="user-info">
                        <div class="name" id="nav-user-name">Your name</div>
                        <div class="email" id="nav-user-email">yourname@example.com</div>
                    </div>
                </div>

                <!-- Navigation Links (Ticket Service Focus) -->
                <ul class="nav-list">
                   <li class="nav-item">
                        <button id="nav-homepage" onclick="window.location.href='passenger_dashboard.php'">
                            <span style="display: flex; align-items: center;">
                            <i class="fa-solid fa-house-chimney"></i> Homepage
                            </span>
                            <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </button>
                        </li>
                    <li class="nav-item">
                        <button id="nav-vehicles" onclick="window.location.href='vehicle.php'">
                            <span style="display: flex; align-items: center;"><i class="fa-solid fa-bus"></i>Vehicles</span>
                            <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button id="nav-buyticket" onclick="window.location.href='ticketing.php'">
                            <span style="display: flex; align-items: center;"><i class="fa-solid fa-ticket"></i>Buy Ticket</span>
                            <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button id="nav-redeemvoucher" onclick="window.location.href='redeem_voucher.php'">
                            <span style="display: flex; align-items: center;"><i class="fa-solid fa-gift"></i>Redeem Voucher</span>
                            <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button id="nav-feedback" onclick="window.location.href='Feedback.php'">
                            <span style="display: flex; align-items: center;"><i class="fa-solid fa-comment-dots"></i>Feedback</span>
                            <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button id="nav-aboutus" onclick="window.location.href='about.php'">
                            <span style="display: flex; align-items: center;"><i class="fa-solid fa-circle-info"></i>About Us</span>
                            <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Bottom Navigation/Logout -->
            <ul class="nav-list-bottom">
                <li class="nav-item">
                    <!-- My Profile button is now the active element pointing to the right panel -->
                    <button id="nav-profile" class="active" data-view="profile">
                        <span style="display: flex; align-items: center;"><i class="fa-solid fa-user"></i>My Profile / Tickets</span>
                        <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button id="nav-logout" data-view="logout" onclick="showCustomAlert('Logged out successfully!');">
                        <span style="display: flex; align-items: center;"><i class="fa-solid fa-right-from-bracket"></i>Log Out</span>
                    </button>
                </li>
            </ul>
        </div>
        
        <!-- Content Card (Right Panel - Focused on Profile and History) -->
        <div class="content-card">
            <button class="close-btn" onclick="document.querySelector('.app-container').style.display='none';"><i class="fa-solid fa-xmark"></i></button>
            
            <div id="profile-view">
                <h2>Profile Detail</h2>
                
                <!-- Name Row (Kept as requested) -->
                <div class="detail-row">
                    <span class="label">Name</span>
                    <span class="value">
                        <input type="text" id="input-name" value="Your name">
                        <i class="fa-solid fa-pencil edit-icon"></i>
                    </span>
                </div>
                
                <button class="save-btn" id="save-profile-btn">Save Change</button>
                
                <div style="clear: both; padding-top: 10px;"></div>

                <div class="ticket-history-container">
                    <h2>Ticket History</h2>
                    <ul class="ticket-list" id="ticket-list">
                        <!-- Tickets will be populated here by JavaScript -->
                    </ul>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Custom Alert Modal (Replaces alert() function) -->
    <div id="custom-alert-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 350px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <p id="alert-message" style="font-size: 16px; margin-bottom: 20px;"></p>
            <button onclick="document.getElementById('custom-alert-modal').style.display='none';" style="background: var(--accent); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">OK</button>
        </div>
    </div>

    <script>
      // Mock Ticket Data
      const mockTickets = [
        { id: 'TKT-003', title: 'Route 101 Delay Compensation', date: '2025-10-25', status: 'resolved' },
        { id: 'TKT-002', title: 'Double Charge on Purchase', date: '2025-10-20', status: 'pending' },
        { id: 'TKT-001', title: 'App Crash on Login', date: '2025-09-15', status: 'open' },
        { id: 'TKT-004', title: 'Bus Pass Renewal Issue', date: '2025-11-01', status: 'resolved' },
        { id: 'TKT-005', title: 'Wrong Departure Time', date: '2025-11-03', status: 'open' },
      ];

      // Initial state management (Simulates user data)
      const userData = {
        name: 'John Doe',
        email: 'john.doe@example.com', // Retained for header display
      };
      
      const navButtons = document.querySelectorAll('.nav-list button, .nav-list-bottom button');

      // Function to show custom alert modal
      function showCustomAlert(message) {
        document.getElementById('alert-message').textContent = message;
        document.getElementById('custom-alert-modal').style.display = 'flex';
      }

      function renderTickets() {
        const ticketList = document.getElementById('ticket-list');
        ticketList.innerHTML = ''; // Clear existing list
        
        mockTickets.forEach(ticket => {
          const statusClass = `status-${ticket.status}`;
          const statusDisplay = ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1);
          
          const listItem = document.createElement('li');
          listItem.className = 'ticket-item';
          listItem.setAttribute('title', `Click to view details for ${ticket.id}`);
          listItem.addEventListener('click', () => showCustomAlert(`Viewing ticket: ${ticket.title} (${ticket.id}). Status: ${statusDisplay}`));
          
          listItem.innerHTML = `
            <div class="ticket-details">
              <div class="ticket-title">${ticket.title}</div>
              <div class="ticket-date">Reported: ${ticket.date}</div>
            </div>
            <span class="ticket-status ${statusClass}">${statusDisplay}</span>
          `;
          
          ticketList.appendChild(listItem);
        });
      }

      function updateUI() {
        // Update nav header
        document.getElementById('nav-user-name').textContent = userData.name;
        document.getElementById('nav-user-email').textContent = userData.email;
        
        // Update Profile View inputs
        document.getElementById('input-name').value = userData.name;
        
        // Render ticket history
        renderTickets();
      }
      
      // Add event listeners for navigation (simplified)
      navButtons.forEach(button => {
          button.addEventListener('click', (e) => {
              const view = e.currentTarget.dataset.view;
              
              // Handle clicks for the new navigation items
              if (view !== 'profile' && view !== 'logout') {
                 showCustomAlert(`Navigating to: ${e.currentTarget.textContent.trim().split(' ')[0]} Page`);
              } else if (view === 'logout') {
                  // Logout handled by inline onclick
                  // For other main items, the right panel remains as is
              }
              
              // Ensure My Profile / Tickets button remains visually active as it controls the right panel content
              document.getElementById('nav-profile').classList.add('active');
              
              // Give the other buttons a slight visual feedback on click
              if (view !== 'profile') {
                e.currentTarget.classList.add('temporary-active');
                setTimeout(() => e.currentTarget.classList.remove('temporary-active'), 300);
              }
          });
      });
      
      // Save Profile Changes
      document.getElementById('save-profile-btn').addEventListener('click', () => {
          const newName = document.getElementById('input-name').value.trim();
          
          if (!newName) {
              showCustomAlert('Name cannot be empty.');
              return;
          }
          
          userData.name = newName;
          
          updateUI();
          showCustomAlert('Profile name saved successfully!');
      });

      // Initialize UI on load
      updateUI();
    </script>
  </body>
</html>
