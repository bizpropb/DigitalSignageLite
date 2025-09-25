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
      {showRegistrationForm && !isRegistered && (
        <div className="registration-form">
          <div className="form-group">
            <input
              type="text"
              value={accessToken}
              onChange={(e) => onAccessTokenChange(e.target.value.toUpperCase())}
              placeholder="Enter 6-char token"
              maxLength="6"
              className="input"
            />
            <button
              onClick={onConnect}
              disabled={accessToken.length !== 6}
              className={`button button-primary ${accessToken.length !== 6 ? 'button-primary-disabled' : ''}`}
            >
              Connect
            </button>
          </div>
        </div>
      )}

      {/* Display Information */}
      <div className="display-info-card">
        <h3 className="h3 text-accent">Display Registration</h3>
        <div className="info-grid">
          <div className="info-item">
            <strong className="label">Name:</strong> <span className="value">{displayInfo.name || 'Not registered'}</span>
          </div>
          <div className="info-item">
            <strong className="label">Program:</strong> <span className="value">{displayInfo.program?.name || displayInfo.program || 'Not set'}</span>
          </div>
          <div className="info-item">
            <strong className="label">Location:</strong> <span className="value">{displayInfo.location || 'Not registered'}</span>
          </div>
          <div className="info-item">
            <strong className="label">Access Token:</strong> <span className="value">{displayInfo.access_token || 'Not connected'}</span>
          </div>
          <div className="info-item">
            <strong className="label">Created:</strong> <span className="value">{displayInfo.created_at ? new Date(displayInfo.created_at).toLocaleDateString('de-DE') : 'Unknown'}</span>
          </div>
        </div>
        
        {isRegistered && (
          <div className="success-message">
            ✅ Display successfully connected
          </div>
        )}
      </div>

    </>
  )
}

export default DisplayInfo