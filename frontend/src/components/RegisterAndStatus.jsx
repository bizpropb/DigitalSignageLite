import { useState, useEffect } from 'react'
import Pusher from 'pusher-js'

function RegisterAndStatus() {
  const [connectionStatus, setConnectionStatus] = useState('disconnected')
  const [messages, setMessages] = useState([])
  const [accessToken, setAccessToken] = useState('')
  const [isRegistered, setIsRegistered] = useState(false)
  const [displayInfo, setDisplayInfo] = useState({
    id: null,
    name: 'Not registered',
    display_type: null,
    location: 'Not registered',
    auth_token: null,
    access_token: null,
    created_at: null
  })

  // Function to find display by access token
  const findDisplayByToken = async () => {
    if (!accessToken || accessToken.length !== 6) {
      alert('Please enter a valid 6-character access token')
      return
    }

    try {
      const response = await fetch(`http://localhost:8000/api/displays/find-by-token`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          access_token: accessToken.toUpperCase(),
        }),
      })

      if (response.ok) {
        const data = await response.json()
        if (data.success) {
          setDisplayInfo({
            id: data.display.id,
            name: data.display.name,
            display_type: data.display.display_type,
            location: data.display.location,
            auth_token: data.display.auth_token,
            access_token: data.display.access_token,
            created_at: data.display.created_at
          })
          // Store auth_token in localStorage for future use
          localStorage.setItem('display_auth_token', data.display.auth_token)
          localStorage.setItem('display_access_token', data.display.access_token)
          setIsRegistered(true)
        } else {
          alert(data.error || 'Display not found')
        }
      } else {
        const errorData = await response.json()
        alert(errorData.error || 'Failed to find display')
      }
    } catch (error) {
      console.error('Failed to find display:', error)
      alert('Network error occurred')
    }
  }

  // Function to check for existing registration using stored auth_token
  const checkExistingRegistration = async () => {
    const storedAuthToken = localStorage.getItem('display_auth_token')
    
    if (storedAuthToken) {
      try {
        const response = await fetch(`http://localhost:8000/api/displays/find-by-auth`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            auth_token: storedAuthToken,
          }),
        })

        if (response.ok) {
          const data = await response.json()
          if (data.success && data.display.initialized) {
            // Display is already initialized, hide connection UI
            setDisplayInfo({
              id: data.display.id,
              name: data.display.name,
              display_type: data.display.display_type,
              location: data.display.location,
              auth_token: data.display.auth_token,
              access_token: data.display.access_token,
              created_at: data.display.created_at
            })
            setIsRegistered(true)
          } else if (data.display && !data.display.initialized) {
            // Display exists but not initialized yet - show access token if we have it
            const storedAccessToken = localStorage.getItem('display_access_token')
            if (storedAccessToken) {
              setAccessToken(storedAccessToken)
            }
          }
        }
      } catch (error) {
        console.error('Failed to check existing registration:', error)
      }
    }
  }

  // Function to send heartbeat
  const sendHeartbeat = async () => {
    if (!displayInfo.id) return
    
    try {
      await fetch(`http://localhost:8000/api/displays/heartbeat`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          display_id: displayInfo.id,
        }),
      })
    } catch (error) {
      console.error('Failed to send heartbeat:', error)
    }
  }

  useEffect(() => {
    // Initialize Pusher connection to Laravel Reverb
    const pusher = new Pusher('local', {
      wsHost: window.location.hostname, // Use same host as React app
      wsPort: 8080,
      wssPort: 8080,
      forceTLS: false,
      enabledTransports: ['ws', 'wss'],
      disableStats: true,
      cluster: 'mt1', // Required by Pusher.js
      encrypted: false,
    })

    // Connection status handling
    pusher.connection.bind('connected', () => {
      setConnectionStatus('connected')
      // Check for existing registration when connected
      checkExistingRegistration()
    })

    pusher.connection.bind('disconnected', () => {
      setConnectionStatus('disconnected')
    })

    pusher.connection.bind('error', (error) => {
      setConnectionStatus('error')
      console.error('WebSocket connection error:', error)
    })

    // Subscribe to display channel for this client
    const channel = pusher.subscribe('display-updates')
    
    // Listen for test messages
    channel.bind('test-message', (data) => {
      // Get stored auth token from localStorage
      const storedAuthToken = localStorage.getItem('display_auth_token')
      
      // Verify auth token before displaying message
      if (data.auth_token && storedAuthToken && data.auth_token === storedAuthToken) {
        console.log('Authenticated Message Received')
        setMessages(prev => [...prev, {
          id: Date.now(),
          message: data.message,
          timestamp: new Date().toLocaleTimeString()
        }])
      } else if (data.auth_token) {
        console.log('Unauthorized message received: wrong key')
      } else {
        // For backward compatibility, show messages without auth_token
        console.log('Message received without authentication')
        setMessages(prev => [...prev, {
          id: Date.now(),
          message: data.message,
          timestamp: new Date().toLocaleTimeString()
        }])
      }
    })

    // Cleanup on unmount (but not in React StrictMode double-mount)
    return () => {
      if (channel) {
        pusher.unsubscribe('display-updates')
      }
      // Don't disconnect in development to avoid StrictMode issues
      if (import.meta.env.PROD) {
        pusher.disconnect()
      }
    }
  }, [])

  // Heartbeat effect - send heartbeat every 60 seconds when connected
  useEffect(() => {
    if (connectionStatus !== 'connected' || !displayInfo.id) return

    const heartbeatInterval = setInterval(sendHeartbeat, 60000) // 60 seconds
    
    return () => clearInterval(heartbeatInterval)
  }, [connectionStatus, displayInfo.id])

  const getStatusDisplay = () => {
    const statusConfig = {
      connected: {
        color: '#10b981',
        backgroundColor: '#ecfdf5',
        borderColor: '#a7f3d0',
        icon: '🟢',
        text: 'Connected'
      },
      disconnected: {
        color: '#ef4444', 
        backgroundColor: '#fef2f2',
        borderColor: '#fecaca',
        icon: '🔴',
        text: 'Disconnected'
      },
      error: {
        color: '#f59e0b',
        backgroundColor: '#fffbeb', 
        borderColor: '#fed7aa',
        icon: '🟡',
        text: 'Connection Error'
      }
    }
    
    return statusConfig[connectionStatus] || statusConfig.disconnected
  }

  const statusDisplay = getStatusDisplay()

  return (
    <div style={{ padding: '20px', fontFamily: 'system-ui, -apple-system, sans-serif' }}>
      {/* Header */}
      <div style={{ 
        display: 'flex', 
        justifyContent: 'space-between', 
        alignItems: 'center', 
        marginBottom: '30px',
        borderBottom: '1px solid #e5e7eb',
        paddingBottom: '20px'
      }}>
        <div>
          <h1 style={{ margin: 0, color: 'black' }}>Presenter V3</h1>
          <p style={{ margin: '5px 0 0 0', color: '#6b7280', fontSize: '14px' }}>
            Digital Signage Display Client
          </p>
        </div>
        
        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
          {!isRegistered && connectionStatus === 'connected' && (
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <input
                type="text"
                value={accessToken}
                onChange={(e) => setAccessToken(e.target.value.toUpperCase())}
                placeholder="Enter 6-char token"
                maxLength="6"
                style={{
                  padding: '8px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: '8px',
                  fontSize: '14px',
                  width: '140px',
                  textAlign: 'center',
                  textTransform: 'uppercase'
                }}
              />
              <button
                onClick={findDisplayByToken}
                disabled={accessToken.length !== 6}
                style={{
                  backgroundColor: accessToken.length === 6 ? '#dc2626' : '#9ca3af',
                  color: 'white',
                  border: 'none',
                  padding: '8px 16px',
                  borderRadius: '8px',
                  fontSize: '14px',
                  fontWeight: '500',
                  cursor: accessToken.length === 6 ? 'pointer' : 'not-allowed',
                  transition: 'background-color 0.2s'
                }}
                onMouseOver={(e) => {
                  if (accessToken.length === 6) {
                    e.target.style.backgroundColor = '#b91c1c'
                  }
                }}
                onMouseOut={(e) => {
                  if (accessToken.length === 6) {
                    e.target.style.backgroundColor = '#dc2626'
                  }
                }}
              >
                Connect
              </button>
            </div>
          )}
          
          <div style={{ 
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            padding: '8px 16px', 
            backgroundColor: statusDisplay.backgroundColor,
            color: statusDisplay.color,
            border: `1px solid ${statusDisplay.borderColor}`,
            borderRadius: '8px',
            fontSize: '14px',
            fontWeight: '500'
          }}>
            <span>{statusDisplay.icon}</span>
            <span>{statusDisplay.text}</span>
          </div>
        </div>
      </div>

      {/* Display Information */}
      <div style={{
        backgroundColor: '#f9fafb',
        border: '1px solid #e5e7eb',
        borderRadius: '8px',
        padding: '20px',
        marginBottom: '30px'
      }}>
        <h3 style={{ margin: '0 0 15px 0', color: '#374151' }}>Display Registration</h3>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '15px', marginBottom: '20px' }}>
          <div>
            <strong style={{ color: '#6b7280' }}>Name:</strong> {displayInfo.name}
          </div>
          <div>
            <strong style={{ color: '#6b7280' }}>Type:</strong> {displayInfo.display_type || 'Not set'}
          </div>
          <div>
            <strong style={{ color: '#6b7280' }}>Location:</strong> {displayInfo.location}
          </div>
          <div>
            <strong style={{ color: '#6b7280' }}>Access Token:</strong> {displayInfo.access_token || 'Not connected'}
          </div>
          <div>
            <strong style={{ color: '#6b7280' }}>Created:</strong> {displayInfo.created_at ? new Date(displayInfo.created_at).toLocaleDateString('de-DE') : 'Unknown'}
          </div>
        </div>
        
        {isRegistered && (
          <div style={{
            marginTop: '15px',
            padding: '12px',
            backgroundColor: '#dcfce7',
            border: '1px solid #bbf7d0',
            borderRadius: '6px',
            color: '#166534',
            fontSize: '14px'
          }}>
            ✅ Display successfully connected
          </div>
        )}
      </div>

      {/* Messages */}
      <div style={{
        backgroundColor: '#ffffff',
        border: '1px solid #e5e7eb',
        borderRadius: '8px',
        padding: '20px'
      }}>
        <h3 style={{ margin: '0 0 15px 0', color: '#374151' }}>Test-Message-System</h3>
        {messages.length === 0 ? (
          <p style={{ color: '#6b7280', fontStyle: 'italic' }}>
            No messages received yet.
            <br />Send a test message from the admin panel.
            <br />Messages are temporary and not saved in the database.
          </p>
        ) : (
          <div style={{ maxHeight: '300px', overflowY: 'auto' }}>
            {messages.map(msg => (
              <div key={msg.id} style={{ 
                padding: '10px',
                borderBottom: '1px solid #f3f4f6',
                fontSize: '14px'
              }}>
                <div style={{ color: '#6b7280', fontSize: '12px' }}>
                  {msg.timestamp}
                </div>
                <div style={{ color: '#374151', marginTop: '4px' }}>
                  {msg.message}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Access Token Info */}
      <div style={{
        marginTop: '20px',
        backgroundColor: '#dbeafe',
        border: '1px solid #3b82f6',
        borderRadius: '8px',
        padding: '16px'
      }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <div style={{ 
            color: '#1d4ed8', 
            marginRight: '12px',
            fontSize: '20px',
            lineHeight: '20px'
          }}>
            ℹ️
          </div>
          <div>
            <div style={{ margin: '0', color: '#1e3a8a', fontSize: '13px', lineHeight: '1.4' }}>
              <strong>Access Token System</strong> 
              <div>
              Get your 6-character access token from the admin panel by creating a new display device. 
              Each token is unique and connects this browser to a specific display configuration.
              Your token is saved locally for reconnection after browser refresh.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default RegisterAndStatus