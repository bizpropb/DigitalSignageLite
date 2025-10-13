// DisplayInfo.jsx
import React from 'react'

const DisplayInfo = ({ 
  displayInfo, 
  isRegistered,
  showRegistrationForm = false,
  accessToken = '',
  onAccessTokenChange = () => {},
  onConnect = () => {}
}) => {

  return (
    <>
      {showRegistrationForm && (
        <div className="form-group" style={{ margin: 0 }}>
          {!isRegistered && (
            <input
              type="text"
              value={accessToken}
              onChange={(e) => onAccessTokenChange(e.target.value.toUpperCase())}
              placeholder="Enter 6-char token"
              maxLength="6"
              className="input"
            />
          )}
          <button
            onClick={onConnect}
            disabled={accessToken.length !== 6 || isRegistered}
            style={isRegistered ? {
              backgroundColor: '#141414',
              color: 'white',
              border: '1px solid #22c55e',
              cursor: 'default',
              fontWeight: 'normal'
            } : {}}
            className={`button button-primary ${accessToken.length !== 6 && !isRegistered ? 'button-primary-disabled' : ''}`}
          >
            {isRegistered ? 'Connected' : 'Connect'}
          </button>
        </div>
      )}

      {/* Display Information */}
      <div style={{ backgroundColor: 'var(--bg-secondary)', border: '1px solid var(--accent-orange)', borderRadius: '6px', padding: '20px', margin: 0 }}>
        <h1>Display Info</h1>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '15px', marginBottom: '20px' }}>
          <div>
            Name: {displayInfo.name || 'Not registered'}
          </div>
          <div>
            Program: {displayInfo.program?.name || displayInfo.program || 'Not set'}
          </div>
          <div>
            Location: {displayInfo.location || 'Not registered'}
          </div>
          <div>
            Access Token: {displayInfo.access_token || 'Not connected'}
          </div>
          <div>
            Created: {displayInfo.created_at ? new Date(displayInfo.created_at).toLocaleDateString('de-DE') : 'Unknown'}
          </div>
        </div>
      </div>

    </>
  )
}

export default DisplayInfo