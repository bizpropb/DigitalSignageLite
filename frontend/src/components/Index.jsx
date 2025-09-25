// Index.jsx
import { Link } from 'react-router-dom'

function Index() {
  return (
    <div className="full-screen-center">
      <div className="content-container">
      <h1 className="h1">
        Presenter V4
      </h1>

      <p className="page-description">
        Modern Digital Signage System Solutions
      </p>


      <div className="message-box">
        <h3 className="h3">Available Routes</h3>

        <div className="grid-container">
          <div className="info-card">
            <strong className="text-accent">Frontend Routes:</strong>
            <div className="text-mono">
              <div className="text-accent">
                <strong>/</strong> - You Are Here // click on a route to navigate to it
              </div>
              <div className="link-route">
                <Link
                  to="/register"
                  className="link-route"
                >
                  <strong>/register</strong> - Register device with system // show status etc.
                </Link>
              </div>
              <div className="link-route">
                <Link
                  to="/live"
                  className="link-route"
                >
                  <strong>/live</strong> - Live content display
                </Link>
              </div>
            </div>
          </div>

          <div className="info-card">
            <strong className="text-accent">Quick Setup:</strong>
            <div className="quick-setup">
              Navigate to <strong>/register</strong> to register device with the system.
              Use <strong>/live</strong> for receiving and displaying content signals.
            </div>
          </div>
        </div>
      </div>

      <div className="message-box">
        <h3 className="h3">Admin Routes</h3>

        <div className="grid-container">
          <div className="info-card">
            <strong className="text-accent">Backend Routes:</strong>
            <div className="text-mono">
              <div className="link-route">
                <strong>http://localhost:8000/admin</strong> - Admin panel
              </div>
              <div className="link-route">
                <strong>http://localhost:8000/api/*</strong> - API endpoints
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  )
}

export default Index