import { useState, useEffect } from 'react'
import Pusher from 'pusher-js'
import * as jose from 'jose'
import DisplayInfo from './DisplayInfo'

function RegisterAndTest() {
  const [connectionStatus, setConnectionStatus] = useState('disconnected')
  const [messages, setMessages] = useState([])
  const [accessToken, setAccessToken] = useState('')
  const [isRegistered, setIsRegistered] = useState(false)
  const [serverPublicKey, setServerPublicKey] = useState(null)
  const [displayInfo, setDisplayInfo] = useState({
    id: null,
    name: 'Not registered',
    program: null,
    location: 'Not registered',
    auth_token: null,
    access_token: null,
    created_at: null
  })
  const [testResults, setTestResults] = useState(Array(50).fill(null))
  const [isTestRunning, setIsTestRunning] = useState(false)
  const [pusherInstance, setPusherInstance] = useState(null)

  // Function to find display by access token
  const findDisplayByToken = async () => {
    if (!accessToken || accessToken.length !== 6) {
      alert('Please enter a valid 6-character access token')
      return
    }

    try {
      const response = await fetch('http://localhost:8000/api/displays/find-by-token', {
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
          //  FIX: Consistent program name access
          console.log('Program data type check:', typeof data.display.program, data.display.program)

          setDisplayInfo({
            id: data.display.id,
            name: data.display.name,
            program: data.display.program,
            location: data.display.location,
            auth_token: data.display.auth_token,
            access_token: data.display.access_token,
            created_at: data.display.created_at
          })
          // Store auth_token in localStorage for future use
          localStorage.setItem('display_auth_token', data.display.auth_token)
          localStorage.setItem('display_access_token', data.display.access_token)
          setIsRegistered(true)

          // Subscribe to display-specific channel for per-display test messages
          setTimeout(() => {
            console.log('Subscribing to display-specific channel for ID:', data.display.id)
            window.subscribeToDisplayChannel?.(data.display.id)
          }, 100)

          // Switch to program-specific channel if program is assigned
          if (data.display.program) {
            // We need to trigger this after state updates, so use a timeout
            setTimeout(() => {
              const programName = data.display.program?.name || data.display.program
              console.log('Switching to program channel:', programName)
              window.switchToProgramChannel?.(programName)
            }, 200)
          }
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
        const response = await fetch('http://localhost:8000/api/displays/find-by-auth', {
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
            //  FIX: Consistent program name access
            console.log('Existing registration program data type check:', typeof data.display.program, data.display.program)

            // Display is already initialized, hide connection UI
            setDisplayInfo({
              id: data.display.id,
              name: data.display.name,
              program: data.display.program,
              location: data.display.location,
              auth_token: data.display.auth_token,
              access_token: data.display.access_token,
              created_at: data.display.created_at
            })
            setIsRegistered(true)

            // Subscribe to display-specific channel for per-display test messages
            setTimeout(() => {
              console.log('Subscribing to display-specific channel for existing display ID:', data.display.id)
              window.subscribeToDisplayChannel?.(data.display.id)
            }, 100)

            // Switch to program-specific channel if program is assigned
            if (data.display.program) {
              setTimeout(() => {
                const programName = data.display.program?.name || data.display.program
                console.log('Switching to program channel on existing registration:', programName)
                window.switchToProgramChannel?.(programName)
              }, 200)
            }
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

  // Function to fetch server public key for JWT verification
  const fetchServerPublicKey = async () => {
    try {
      console.log('Fetching server public key...')
      const response = await fetch('http://localhost:8000/api/public-key')
      console.log('Public key response status:', response.status)

      if (response.ok) {
        const data = await response.json()
        console.log('Public key response data:', data)

        if (data.success) {
          console.log('Setting public key:', data.public_key.substring(0, 50) + '...')
          setServerPublicKey(data.public_key)
          localStorage.setItem('server_public_key', data.public_key)
        } else {
          console.error('Public key fetch failed:', data)
        }
      } else {
        console.error('Public key endpoint returned status:', response.status)
      }
    } catch (error) {
      console.error('Failed to fetch server public key:', error)
    }
  }

  // Function to verify JWT message
  const verifyMessage = async (signedMessage) => {
    // Get public key from state or localStorage as fallback
    let publicKeyToUse = serverPublicKey
    if (!publicKeyToUse) {
      publicKeyToUse = localStorage.getItem('server_public_key')
      console.log('Using fallback public key from localStorage:', publicKeyToUse ? 'Found' : 'Not found')
    }

    if (!publicKeyToUse) {
      console.error('No public key available for verification - state:', !!serverPublicKey, 'localStorage:', !!localStorage.getItem('server_public_key'))
      return null
    }

    try {
      console.log('Attempting to verify JWT with public key length:', publicKeyToUse.length)

      // Convert PEM public key to JOSE format
      const publicKey = await jose.importSPKI(publicKeyToUse, 'RS256')
      console.log('Public key imported successfully')

      // Verify and decode JWT
      const { payload } = await jose.jwtVerify(signedMessage, publicKey)
      console.log('JWT verification successful:', payload)
      return payload.data
    } catch (error) {
      console.error('Message verification failed:', error)
      return null
    }
  }

  // Function to send heartbeat
  const sendHeartbeat = async () => {
    if (!displayInfo.id) return

    try {
      await fetch('http://localhost:8000/api/displays/heartbeat', {
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

  // Function to start connection stability test
  const startStabilityTest = () => {
    if (isTestRunning || !pusherInstance) return

    // Reset results
    setTestResults(Array(50).fill(null))
    setIsTestRunning(true)

    let pingCount = -1 // Ensures the loop starts with Div Index 0, instead of 1

    // Run first check immediately
    const runCheck = () => {
      const state = pusherInstance.connection.state
      const result = state === 'connected' ? 'pass' : 'fail'

      setTestResults(prev => {
        const newResults = [...prev]
        newResults[pingCount] = result
        return newResults
      })

      pingCount++

      if (pingCount >= 50) {
        clearInterval(testInterval)
        setIsTestRunning(false)
      }
    }

    runCheck() // Immediate first check

    const testInterval = setInterval(runCheck, 200) // Then every 200ms
  }

  useEffect(() => {
    // Initialize public key immediately
    const initializePublicKey = async () => {
      const storedPublicKey = localStorage.getItem('server_public_key')
      if (storedPublicKey) {
        console.log('Using stored public key on mount:', storedPublicKey.substring(0, 50) + '...')
        setServerPublicKey(storedPublicKey)
      } else {
        console.log('Fetching public key on mount...')
        await fetchServerPublicKey()
      }
    }

    initializePublicKey()

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

    // Store pusher instance for stability test
    setPusherInstance(pusher)

    //  FIX: Enhanced Pusher state logging
    pusher.connection.bind('state_change', states => {
      console.log('Pusher state:', states.current)
    })

    // Keep track of subscribed channels
    let displayChannel = null // For display-specific messages (display.{id})

    // Connection status handling
    pusher.connection.bind('connected', () => {
      setConnectionStatus('connected')
      // Fetch public key and check registration when connected
      const storedPublicKey = localStorage.getItem('server_public_key')
      if (storedPublicKey) {
        console.log('Using stored public key:', storedPublicKey.substring(0, 50) + '...')
        setServerPublicKey(storedPublicKey)
      } else {
        console.log('No stored public key, fetching...')
        fetchServerPublicKey()
      }
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
    let channel = pusher.subscribe('display-updates')

    // Function to subscribe to display-specific channel for per-display test messages
    const subscribeToDisplayChannel = (displayId) => {
      if (displayId && !displayChannel) {
        //  FIX: Ensure proper template literal with backticks
        const channelName = `display.${displayId}`
        console.log('Subscribing to display-specific channel:', channelName)

        displayChannel = pusher.subscribe(channelName)

        //  FIX: Enhanced subscription logging
        console.log('Subscribed to', channelName, '- binding display.test-message')

        // Handle display-specific test messages (plain payload, no JWT signing)
        displayChannel.bind('display.test-message', (data) => {
          console.log('Received display test message:', data)
          //  FIX: Enhanced message data logging
          console.log('Test message data:', data)

          // Add message to the test messages list (no verification needed for test messages)
          setMessages(prev => [...prev, {
            id: Date.now(),
            message: data.message || 'Test message received',
            timestamp: new Date().toLocaleTimeString(),
            verified: false // Display test messages are not JWT-signed
          }])
        })

        console.log('Display channel subscription successful for channel:', channelName)
      }
    }

    // Function to switch to program-specific channel
    const switchToProgramChannel = (programName) => {
      if (programName && programName !== 'display-updates') {
        console.log('Switching to program channel:', programName)

        // Unsubscribe from current channel
        pusher.unsubscribe(channel.name)

        // Subscribe to program-specific channel
        channel = pusher.subscribe(programName)

        // Re-bind event listeners for the new channel
        channel.bind('content-update', async (data) => {
          //  FIX: Ensure proper template literal with backticks for console.log
          console.log(`Received content update on ${programName} channel:`, data)

          if (data.signed_message) {
            const messageData = await verifyMessage(data.signed_message)

            if (messageData) {
              console.log('Program content update verified:', messageData)
              // Handle actual content display here - NOT in test message system
              // setMessages is only for test messages, not program content
            }
          }
        })

        // Re-bind secure-message listener for program channel
        channel.bind('secure-message', async (data) => {
          console.log('Received secure message on program channel:', data)

          if (data.signed_message) {
            const messageData = await verifyMessage(data.signed_message)

            if (messageData) {
              // Get stored auth token for additional verification
              const storedAuthToken = localStorage.getItem('display_auth_token')

              // Verify auth token matches if present in message
              if (messageData.auth_token && storedAuthToken && messageData.auth_token === storedAuthToken) {
                console.log('Secure message verified and authorized on program channel')
                setMessages(prev => [...prev, {
                  id: Date.now(),
                  message: messageData.message,
                  timestamp: new Date().toLocaleTimeString(),
                  verified: true
                }])
              } else if (!messageData.auth_token) {
                // Broadcast message without specific auth token
                console.log('Secure broadcast message verified on program channel')
                setMessages(prev => [...prev, {
                  id: Date.now(),
                  message: messageData.message,
                  timestamp: new Date().toLocaleTimeString(),
                  verified: true
                }])
              } else {
                console.log('Message verified but not authorized for this display')
              }
            } else {
              console.log('Message signature verification failed - possible spoofing attempt')
            }
          } else {
            console.log('Received unsigned message - ignoring for security')
          }
        })
      }
    }

    // Expose functions globally so they can be called from registration callbacks
    window.switchToProgramChannel = switchToProgramChannel
    window.subscribeToDisplayChannel = subscribeToDisplayChannel

    // Listen for secure messages on default channel
    channel.bind('secure-message', async (data) => {
      console.log('Received secure message:', data)

      if (data.signed_message) {
        const messageData = await verifyMessage(data.signed_message)

        if (messageData) {
          // Get stored auth token for additional verification
          const storedAuthToken = localStorage.getItem('display_auth_token')

          // Verify auth token matches if present in message
          if (messageData.auth_token && storedAuthToken && messageData.auth_token === storedAuthToken) {
            console.log('Secure message verified and authorized')
            setMessages(prev => [...prev, {
              id: Date.now(),
              message: messageData.message,
              timestamp: new Date().toLocaleTimeString(),
              verified: true
            }])
          } else if (!messageData.auth_token) {
            // Broadcast message without specific auth token
            console.log('Secure broadcast message verified')
            setMessages(prev => [...prev, {
              id: Date.now(),
              message: messageData.message,
              timestamp: new Date().toLocaleTimeString(),
              verified: true
            }])
          } else {
            console.log('Message verified but not authorized for this display')
          }
        } else {
          console.log('Message signature verification failed - possible spoofing attempt')
        }
      } else {
        console.log('Received unsigned message - ignoring for security')
      }
    })

    // Cleanup on unmount (but not in React StrictMode double-mount)
    return () => {
      if (channel) {
        pusher.unsubscribe(channel.name)
      }
      if (displayChannel) {
        pusher.unsubscribe(displayChannel.name)
      }
      // Clean up global function references
      delete window.switchToProgramChannel
      delete window.subscribeToDisplayChannel
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


  return (
    <div className="page-container">
      <h1 className="page-title">Presenter V4 - Register & Test</h1>
      <p className="page-description">
        Register your display device and test WebSocket messaging
      </p>
      <DisplayInfo
        displayInfo={displayInfo}
        isRegistered={isRegistered}
        showRegistrationForm={true}
        accessToken={accessToken}
        onAccessTokenChange={setAccessToken}
        onConnect={findDisplayByToken}
      />

      {/* Access Token Information */}
      <div className="access-token-info">
        <div className="info-content">
          <div className="info-icon">
            ℹ️
          </div>
          <div>
            <div className="info-text">
              <strong className="info-strong">Access Token System</strong>
              <div>
              Get your 6-character access token from the admin panel by creating a new display device.
              Each token is unique and connects this browser to a specific display configuration.
              Your token is saved locally for reconnection after browser refresh.
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Connection Stability Test */}
      <div className="messages-container" style={{ marginBottom: '20px' }}>
        <h3 className="h3">Connection Stability Test</h3>

        <div style={{
          display: 'flex',
          flexWrap: 'nowrap',
          marginLeft: '4px',
          gap: '8px',
          height: '40px',
          marginBottom: '10px',
          alignItems: 'stretch'
        }}>
          <button
            onClick={startStabilityTest}
            disabled={isTestRunning || !pusherInstance}
            style={{
              padding: '0px 0px',
              fontSize: '16px',
              width: '240px',
              justifyContent: 'center',
              cursor: isTestRunning ? 'not-allowed' : 'pointer',
              opacity: isTestRunning ? 0.6 : 1,
              height: '100%',
              flexShrink: 0,
              display: 'flex',
              alignItems: 'center',
              borderRadius: '4px',
              backgroundColor: '#141414',
              border: '1px solid #ea580c',
              color: '#ea580c'
            }}
          >
            {isTestRunning ? 'Test Running...' : 'Start Connection Stability Test'}
          </button>

          {testResults.map((result, index) => (
            <div
              key={index}
              style={{
                flex: 1,
                height: '100%',
                borderRadius: '4px',
                backgroundColor: result === 'pass' ? '#22c55e' : result === 'fail' ? '#ef4444' : '#9ca3af',
                transition: 'background-color 0.2s'
              }}
            />
          ))}
        </div>

        {/* Status Text */}
        <p className="no-messages">
          {testResults.some(r => r !== null)
            ? "green means passed, red means failed, interval is 200ms - sporadic connection issues may not affect actual display performance."
            : "Hit the 'Start Connection Stability Test' button above to start the test"
          }
        </p>
      </div>

      {/* Messages */}
      <div className="messages-container">
        <h3 className="h3">Test-Message-System</h3>
        {messages.length === 0 ? (
          <p className="no-messages">
            No messages received yet.
            <br />Send a test message from the admin panel.
            <br />Messages are temporary and not saved in the database.
          </p>
        ) : (
          <div className="log-container">
            {messages.map(msg => (
              <div key={msg.id} className="message-entry">
                <div className="message-time">
                  <span>{msg.timestamp}</span>
                  {msg.verified && (
                    <span className="verified-badge">
                      🔒 VERIFIED
                    </span>
                  )}
                </div>
                <div className="message-content">
                  {msg.message}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

export default RegisterAndTest