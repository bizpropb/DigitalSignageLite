import { Link } from 'react-router-dom'

function Index() {
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      height: '100vh',
      padding: '0'
    }}>
      <div style={{ 
        fontFamily: 'system-ui, -apple-system, sans-serif',
        maxWidth: '1200px',
        textAlign: 'center'
      }}>
      <h1 style={{ 
        fontSize: '2.5rem', 
        margin: '0px',
        color: '#1f2937'
      }}>
        Presenter V3
      </h1>
      
      <p style={{ 
        fontSize: '1rem', 
        color: '#6b7280',
        marginBottom: '20px',
        lineHeight: '1.6'
      }}>
        Modern Digital Signage System Solutions
      </p>


      <div style={{
        backgroundColor: '#f9fafb',
        padding: '10px',
        borderRadius: '8px',
        border: '1px solid #e5e7eb',
        height: 'auto',
        overflowY: 'auto'
      }}>
        <h3 style={{ margin: '0 0 15px 0', color: '#374151' }}>Available Routes</h3>
        
        <div style={{ 
          display: 'grid', 
          gap: '12px',
          textAlign: 'left'
        }}>
          <div style={{
            padding: '12px',
            backgroundColor: '#e0f2fe',
            borderRadius: '6px',
            border: '1px solid #0284c7'
          }}>
            <strong style={{ color: '#0c4a6e', fontSize: '24px' }}>Frontend Routes:</strong>
            <div style={{ fontSize: '20px', fontFamily: 'monospace' }}>
              <div style={{ color: '#8b5cf6', fontWeight: 'bold' }}>
                <strong>/</strong> - You Are Here // click on a route to navigate to it
              </div>
              <div style={{ color: '#0369a1' }}>
                <Link to="/register" style={{ color: '#0369a1', textDecoration: 'none' }}>
                  <strong>/register</strong> - Register device with system // show status etc.
                </Link>
              </div>
              <div style={{ color: '#0369a1' }}>
                <Link to="/live" style={{ color: '#0369a1', textDecoration: 'none' }}>
                  <strong>/live</strong> - Live content display
                </Link>
              </div>
            </div>
          </div>

          <div style={{
            padding: '12px',
            backgroundColor: '#f0fdf4',
            borderRadius: '6px',
            border: '1px solid #16a34a'
          }}>
            <strong style={{ color: '#15803d', fontSize: '24px' }}>Backend Routes:</strong>
            <div style={{ fontSize: '20px', fontFamily: 'monospace' }}>
              <div style={{ color: '#166534' }}>
                <strong>http://localhost:8000/admin</strong> - Admin panel
              </div>
              <div style={{ color: '#166534' }}>
                <strong>http://localhost:8000/api/*</strong> - API endpoints
              </div>
            </div>
          </div>

          <div style={{
            padding: '12px',
            backgroundColor: '#fef3c7',
            borderRadius: '6px',
            border: '1px solid #f59e0b'
          }}>
            <strong style={{ color: '#92400e', fontSize: '24px' }}>Quick Setup:</strong>
            <div style={{ fontSize: '18px', color: '#78350f' }}>
              Navigate to <strong>/register</strong> to register device with the system.
              Use <strong>/live</strong> for receiving and displaying content signals.
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  )
}

export default Index