// Index.jsx
import { Link } from 'react-router-dom'

function Index() {
  return (
    <div style={{ padding: '20px' }}>
      <div style={{ maxWidth: '1200px', margin: '0 auto' }}>
      <h1>
        Presenter V4
      </h1>

      <p style={{ marginBottom: '15px' }}>
        Lightweight Digital Signage Solutions
      </p>


      <div className="message-box">
        <h3 className="h3">Available Routes</h3>

        <div className="grid-container">
          <div className="info-card">
            <strong className="text-accent">Frontend Routes:</strong>
            <div className="text-mono">
              <Link to="/register" className="link-route">
                <strong>/register</strong> - Register device, show status, testing suite
              </Link>
              <Link to="/live" className="link-route">
                <strong>/live</strong> - Live content display
              </Link>
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
              <a href="http://localhost:8000/admin" target="_blank" rel="noopener noreferrer" className="link-route">
                <strong>http://localhost:8000/admin</strong> - Admin panel
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  )
}

export default Index