<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organiser Dashboard</title>
    <link rel="stylesheet" href="../Participant-css/dashboard.css" />
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
                <h2>Partcipant Dashboard</h2>
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
        <h3 class="main--title"></h1>
        <div class="card--wrapper">
            <div class="competition--card light-red">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Total Competition
                        </span>
                        <span class="amount--value">5</span>
                    </div>
                    <i class="fas fa-trophy icon"></i>
                </div>
                <span class="card--detail">Active Competition</span>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Pending Submission
                        </span>
                        <span class="amount--value">2</span>
                    </div>
                    <i class="fa-solid fa-paper-plane icon dark-purple"></i>
                </div>
                 <span class="card--detail">Awaiting your entry</span>
                 
            </div>

            <div class="competition--card light-blue">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Graded
                        </span>
                        <span class="amount--value">1</span>
                    </div>
                    <i class="fa-solid fa-clock icon dark-blue"></i>
                </div>
                 <span class="card--detail">Results Available</span>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Awards Won
                        </span>
                        <span class="amount--value">2</span>
                    </div>
                    <i class="fa-solid fa-medal icon dark-purple"></i>
                </div>
                 <span class="card--detail">Top 3 Placement</span>
            </div>

        </div>
    </div>
        <div class="card--container">
            <!-- Active Registrations -->
<div class="card--container">
    <h3 class="main--title">Active Registrations (2)</h3>

    <div class="list--item">
        <div class="list--left">
            <h4>Creative Design Challenge</h4>
            <p>Art</p>
            <span>Registered: Dec 15, 2025 | Deadline: Jan 31, 2026</span>
        </div>

        <div class="list--right">
            <div class="days">37<br><small>days left</small></div>
            <button class="btn-submit">
                <i class="fa-solid fa-paper-plane"></i> Submit Entry
            </button>
        </div>
    </div>

    <div class="list--item">
        <div class="list--left">
            <h4>Innovation Tech Summit</h4>
            <p>Technology</p>
            <span>Registered: Dec 20, 2025 | Deadline: Jan 30, 2026</span>
        </div>

        <div class="list--right">
            <div class="days">36<br><small>days left</small></div>
            <button class="btn-submit">
                <i class="fa-solid fa-paper-plane"></i> Submit Entry
            </button>
        </div>
    </div>
</div>


<!-- My Submissions -->
    <div class="card--container">
        <h3 class="main--title">My Submissions (3)</h3>

                <div class="list--item">
                    <div class="list--left">
                        <h4>Global Hackathon 2025</h4>
                        <p>Coding</p>
                        <span>Submitted: Dec 20, 2025 | Score: 87/100 ⭐ Rank #12</span>
                    </div>

                    <div class="list--right">
                        <span class="badge success">Graded</span>
                        <button class="btn-view">
                            <i class="fa-solid fa-eye"></i> View Results
                        </button>
                    </div>
                </div>

                <div class="list--item">
                    <div class="list--left">
                        <h4>Photography Excellence Awards</h4>
                        <p>Photography</p>
                        <span>Submitted: Dec 22, 2025</span>
                    </div>

                    <div class="list--right">
                        <span class="badge warning">Pending Grading</span>
                    </div>
                </div>

                <div class="list--item">
                    <div class="list--left">
                        <h4>Creative Writing Contest</h4>
                        <p>Writing</p>
                        <span>Submitted: Dec 18, 2025</span>
                    </div>

                    <div class="list--right">
                        <span class="badge info">Under Review</span>
                    </div>
                </div>
            </div>
        </div>
    

    </div>


</body>
</html>