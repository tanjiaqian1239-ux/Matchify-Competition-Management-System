<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../Admin-css/dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="sidebar">
        <div class="logo"></div>
        <ul class="menu">
            <li class="active">
                <a href="#" >
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="logout">
                <a href="#" >
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main--content">
        <div class="header--wrapper">
            <div class="header--title">
                <span>Primary</span>
                <h2>Admin Dashboard</h2>
            </div>
            <div class="user--info">
                <div class="search--box">
                <i class="fa-solid fa-search">
                    <input type="text" placeholder="Search" />
                </i>
                </div>
                <img src="../images/profile.avif" alt="" />
            </div>
        </div>
    
    <div class="card--container">
        <div class="card--wrapper">
            <div class="competition--card light-red">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Total Users
                        </span>
                        <span class="amount--value">5</span>
                    </div>
                    <i class="fa-solid fa-users icon"></i>
                </div>
                <span class="card--detail">Active Competition</span>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Pending Proposals
                        </span>
                        <span class="amount--value">2</span>
                    </div>
                    <i class="fa-solid fa-clock icon dark-purple"></i>
                </div>
                 <span class="card--detail">Awaiting your entry</span>
                 
            </div>

            <div class="competition--card light-blue">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Pending Results
                        </span>
                        <span class="amount--value">1</span>
                    </div>
                    <i class="fa-solid fa-hourglass-half  icon dark-blue"></i>
                </div>
                 <span class="card--detail">Results Available</span>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Active Judges
                        </span>
                        <span class="amount--value">2</span>
                    </div>
                    <i class="fa-solid fa-user-check icon dark-purple"></i>
                </div>
                 <span class="card--detail">Top 3 Placement</span>
            </div>

        </div>
    </div>

    <!-- Tabs -->
<div class="tab--container">
    <div class="tab active">
        Competition Proposals <span class="tab-badge orange">3</span>
    </div>
    <div class="tab">
        Result Verification <span class="tab-badge purple">2</span>
    </div>
    <div class="tab">
        User Management
    </div>
</div>

<!-- Competition Proposals Section -->
<div class="card--container">
    <div class="section--header">
        <h3 class="main--title">Competition Proposals</h3>
        <span class="pending-label">3 Pending</span>
    </div>

    <div class="proposal--card">
        <div class="proposal--left">
            <h4>Global Innovation Challenge 2025</h4>
            <span class="tag">Technology</span>

            <p class="organizer">Tech Innovators Inc.</p>
            <p class="email">contact@techinnovators.com</p>

            <div class="meta">
                <span><i class="fa-solid fa-calendar"></i> Jan 15, 2026 - Mar 15, 2026</span>
                <span><i class="fa-solid fa-users"></i> 500 expected participants</span>
            </div>

            <p class="desc">
                A worldwide competition focusing on breakthrough innovations in AI, IoT, and sustainable technology.
            </p>
        </div>

        <div class="proposal--right">
            <button class="approve">
                <i class="fa-solid fa-check"></i> Approve
            </button>
            <button class="reject">
                <i class="fa-solid fa-xmark"></i> Reject
            </button>
        </div>
    </div>
</div>
</div>

</body>
</html>