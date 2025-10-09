// LiveDisplay.jsx
import React, { useState, useEffect, useRef, useMemo } from 'react'
import Pusher from 'pusher-js'
import * as jose from 'jose'
import DisplayInfo from './DisplayInfo'

const LiveDisplay = () => {
  const [connectionStatus, setConnectionStatus] = useState('disconnected')
  const [currentContent, setCurrentContent] = useState(null)
  const [isOffline, setIsOffline] = useState(false)
  const [displayInfo, setDisplayInfo] = useState({ program: null, name: null, location: null, auth_token: null, access_token: null })
  const [serverPublicKey, setServerPublicKey] = useState(null)
  const [currentChannel, setCurrentChannel] = useState('none')
  const [iframeLoaded, setIframeLoaded] = useState(false)
  const [systemLogs, setSystemLogs] = useState([])
  const iframeRef = useRef(null)
  const animationRef = useRef(null)
  const offlineTimeoutRef = useRef(null)
  const countdownIntervalRef = useRef(null)
  const pusherRef = useRef(null) // Ref to track pusher instance
  const logContainerRef = useRef(null) // Ref for log container auto-scroll
  const lastEmbedCodeRef = useRef(null) // Track last embed code to prevent re-injection
  const offlineDisplayTimestamp = useRef(null) // Track when offline overlay was shown

  // Add system log entry
  const addSystemLog = (message) => {
    const timestamp = new Date().toLocaleTimeString()
    setSystemLogs(prev => [...prev, { time: timestamp, message }].slice(-20))
    console.log(`[System Log] ${timestamp}: ${message}`)
    
    // Auto-scroll to bottom after state update
    setTimeout(() => {
      if (logContainerRef.current) {
        logContainerRef.current.scrollTop = logContainerRef.current.scrollHeight
      }
    }, 0)
  }

  // Function to fetch server public key for JWT verification
  const fetchServerPublicKey = async () => {
    try {
      addSystemLog('🔑 Fetching server public key...')
      console.log('Fetching server public key...')
      const response = await fetch(`http://localhost:8000/api/public-key`)
      console.log('Public key response status:', response.status)
      
      if (response.ok) {
        const data = await response.json()
        console.log('Public key response data:', data)
        
        if (data.success) {
          addSystemLog('✅ Public key fetched successfully')
          console.log('Setting public key:', data.public_key.substring(0, 50) + '...')
          setServerPublicKey(data.public_key)
          localStorage.setItem('server_public_key', data.public_key)
        } else {
          addSystemLog('❌ Public key fetch failed: ' + JSON.stringify(data))
          console.error('Public key fetch failed:', data)
        }
      } else {
        addSystemLog(`❌ Public key endpoint error: ${response.status}`)
        console.error('Public key endpoint returned status:', response.status)
      }
    } catch (error) {
      addSystemLog(`❌ Public key fetch error: ${error.message}`)
      console.error('Failed to fetch server public key:', error)
    }
  }

  // Function to verify JWT message
  const verifyMessage = async (signedMessage) => {
    // Get public key from state or localStorage as fallback
    let publicKeyToUse = serverPublicKey
    if (!publicKeyToUse) {
      publicKeyToUse = localStorage.getItem('server_public_key')
      addSystemLog(`Using fallback public key from localStorage: ${publicKeyToUse ? 'Found' : 'Not found'}`)
      console.log('Using fallback public key from localStorage:', publicKeyToUse ? 'Found' : 'Not found')
    }

    if (!publicKeyToUse) {
      addSystemLog('❌ No public key available for verification')
      console.error('No public key available for verification - state:', !!serverPublicKey, 'localStorage:', !!localStorage.getItem('server_public_key'))
      return null
    }

    try {
      addSystemLog('🔐 Attempting JWT verification')
      console.log('Attempting to verify JWT with public key length:', publicKeyToUse.length)
      
      // Convert PEM public key to JOSE format
      const publicKey = await jose.importSPKI(publicKeyToUse, 'RS256')
      addSystemLog('✅ Public key imported successfully')
      console.log('Public key imported successfully')
      
      // Verify and decode JWT
      const { payload } = await jose.jwtVerify(signedMessage, publicKey)
      addSystemLog(`✅ JWT verification successful: ${JSON.stringify(payload)}`)
      console.log('JWT verification successful:', payload)
      return payload.data
    } catch (error) {
      addSystemLog(`❌ Message verification failed: ${error.message}`)
      console.error('Message verification failed:', error)
      return null
    }
  }

  // Function to fetch display info
  const fetchDisplayInfo = async () => {
    addSystemLog('🔄 Fetching display info...')
    const storedAuthToken = localStorage.getItem('display_auth_token');
    
    if (storedAuthToken) {
      try {
        const response = await fetch(`http://localhost:8000/api/displays/find-by-auth`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ auth_token: storedAuthToken }),
        });
        
        console.log('Fetch display info response status:', response.status);
        
        if (response.ok) {
          const data = await response.json();
          console.log('Fetch display info data:', data);
          if (data.success && data.display) {
            const newDisplayInfo = {
              id: data.display.id,
              program: data.display.program?.name || null,
              name: data.display.name,
              location: data.display.location,
              auth_token: data.display.auth_token,
              access_token: data.display.access_token
            }
            
            setDisplayInfo(newDisplayInfo);
            addSystemLog(`✅ Display info updated: ${newDisplayInfo.name} (${newDisplayInfo.program})`)
          } else {
            addSystemLog('⚠️ No display found or no program assigned: ' + JSON.stringify(data))
          }
        } else {
          addSystemLog(`❌ API error: ${response.status}`)
        }
      } catch (error) {
        addSystemLog(`❌ Fetch error: ${error.message}`)
        console.error('Fetch display info error:', error);
      }
    } else {
      addSystemLog('❌ No auth token found')
      console.log('LiveDisplay: Not registered');
    }
    
  };

  // Initialize authentication and fetch display info
  useEffect(() => {
    const initialize = async () => {
      const storedPublicKey = localStorage.getItem('server_public_key');
      if (storedPublicKey) {
        setServerPublicKey(storedPublicKey);
        addSystemLog('✅ Loaded stored public key');
      } else {
        await fetchServerPublicKey();
      }

      await fetchDisplayInfo();
    };
    initialize();

    // Fetch display info every 60 seconds to check for program changes
    const interval = setInterval(fetchDisplayInfo, 60000);
    
    return () => {
      clearInterval(interval)
    };
  }, []);

  // WebSocket connection setup
  useEffect(() => {
    addSystemLog(`Initializing WebSocket (program: ${displayInfo.program || 'none'})`); // Log initialization with current program
    console.log('LiveDisplay: Initializing WebSocket connection'); // Debug log

    if (pusherRef.current) { // If there's an existing WebSocket connection
      addSystemLog('Disconnecting previous Pusher instance'); // Log disconnection of old connection
      pusherRef.current.disconnect(); // Disconnect old connection to prevent multiple connections
    }

    // CREATE NEW WEBSOCKET CONNECTION - These settings MUST match Laravel Reverb server config
    const pusher = new Pusher('local', { // App key - must match PUSHER_APP_KEY in Laravel
      wsHost: window.location.hostname, // WebSocket host - dynamically use current hostname
      wsPort: 8080, // WebSocket port - MUST match Laravel Reverb server port
      wssPort: 8080, // Secure WebSocket port - MUST match Laravel Reverb server port
      forceTLS: false, // Disable TLS for local development - MUST match server config
      enabledTransports: ['ws', 'wss'], // Allowed transport protocols
      disableStats: true, // Disable Pusher analytics for performance
      cluster: 'mt1', // Cluster name - must match Laravel config
      encrypted: false, // Disable encryption for local development
    })
    pusherRef.current = pusher; // Store connection reference for cleanup

    // WEBSOCKET CONNECTION STATE MONITORING - Track all connection state changes
    pusher.connection.bind('state_change', (states) => {
      addSystemLog(`🔄 WebSocket state: ${states.previous} => ${states.current}`); // Log state transitions for debugging
      console.log('WebSocket state change:', states); // Debug log state object
    });

    // WEBSOCKET CONNECTED EVENT - Fired when connection is established
    pusher.connection.bind('connected', () => {
      addSystemLog('🔌 WebSocket connected'); // Log successful connection
      console.log('LiveDisplay: WebSocket connected'); // Debug log
      setConnectionStatus('connected'); // Update UI connection status

      // Respect 5-second minimum display lock
      if (offlineDisplayTimestamp.current) {
        const elapsedTime = Date.now() - offlineDisplayTimestamp.current
        const remainingTime = 5000 - elapsedTime

        if (remainingTime > 0) {
          addSystemLog(`⏳ Waiting ${Math.ceil(remainingTime/1000)}s before hiding offline overlay`)
          setTimeout(() => {
            setIsOffline(false)
            offlineDisplayTimestamp.current = null
          }, remainingTime)
        } else {
          setIsOffline(false)
          offlineDisplayTimestamp.current = null
        }
      }

      if (offlineTimeoutRef.current) { // Clear any pending offline timeout
        clearTimeout(offlineTimeoutRef.current); // Cancel offline detection timer
      }
    });

    // WEBSOCKET DISCONNECTED EVENT - Fired when connection is lost
    pusher.connection.bind('disconnected', () => {
      addSystemLog('🔌 WebSocket disconnected'); // Log disconnection
      console.log('LiveDisplay: WebSocket disconnected'); // Debug log
      setConnectionStatus('disconnected'); // Update UI connection status

      // Immediately show offline overlay and record timestamp
      setIsOffline(true)
      offlineDisplayTimestamp.current = Date.now()
      addSystemLog('⚠️ Displaying offline overlay (locked for 5s minimum)')
    });

    // WEBSOCKET ERROR EVENT - Fired when connection errors occur
    pusher.connection.bind('error', (error) => {
      addSystemLog(`❌ WebSocket error: ${error?.error?.data?.message || error.message || 'Unknown'}`); // Log error details
      console.error('LiveDisplay: WebSocket connection error:', error); // Console error log with full error object
      setConnectionStatus('error'); // Update UI connection status to error

      // Immediately show offline overlay and record timestamp
      setIsOffline(true)
      offlineDisplayTimestamp.current = Date.now()
      addSystemLog('⚠️ Displaying offline overlay (locked for 5s minimum)')
    });

    // WEBSOCKET CHANNEL SUBSCRIPTION - CRITICAL FOR RECEIVING CONTENT UPDATES
    // Channel selection logic determines which messages this display will receive
    const channelName = displayInfo.id ? `display-${displayInfo.id}` : 'display-updates'; // Use display-specific channel if ID exists, otherwise fallback
    addSystemLog(`Subscribed to channel: ${channelName}`); // Log channel subscription
    console.log('LiveDisplay: Subscribing to channel:', channelName); // Debug log channel name
    const channel = pusher.subscribe(channelName); // Subscribe to the determined channel

    // CHANNEL SUBSCRIPTION STATUS EVENTS - Monitor subscription success/failure
    channel.bind('pusher:subscription_succeeded', () => {
      addSystemLog(`✅ Subscription succeeded: ${channelName}`); // Log successful channel subscription
      console.log(`Subscription to ${channelName} succeeded`); // Debug log
    });

    channel.bind('pusher:subscription_error', (status) => {
      addSystemLog(`❌ Subscription error: ${channelName} - Status: ${status}`); // Log subscription failure
      console.error(`Subscription to ${channelName} failed:`, status); // Console error log
    });

    /**
     * WEBSOCKET CONTENT UPDATE HANDLER - MAIN MESSAGE RECEIVER
     * This is where WebSocket content updates are processed
     * Called when server broadcasts new content to this display's channel
     *
     * MESSAGE FLOW:
     * 1. Receive 'content-update' event from server
     * 2. Extract signed_message (JWT token) from data
     * 3. Verify JWT using RSA public key
     * 4. If verification succeeds, update currentContent state
     * 5. Content change triggers iframe reload and scroll animation
     *
     * CRITICAL FAILURE POINTS:
     * - No signed_message in data (server error)
     * - JWT verification fails (security/key issues)
     * - Malformed content data
     */
    channel.bind('content-update', async (data) => {
      addSystemLog(`📨 Content update received on ${channelName}: ${JSON.stringify(data)}`); // Log incoming WebSocket message
      console.log('LiveDisplay: Received content update:', data); // Debug log full message data
      if (data.signed_message) { // Check if message contains JWT token
        addSystemLog('🔐 Verifying JWT message...'); // Log verification attempt
        const messageData = await verifyMessage(data.signed_message); // Verify JWT and extract content
        if (messageData?.content) { // Check if verification succeeded and contains content
          setCurrentContent(messageData.content); // UPDATE CONTENT STATE - This triggers content display
          addSystemLog(`✅ Content verified: ${messageData.content.type} - ${messageData.content.url?.substring(0, 50)}...`); // Log successful content update
          resetOfflineTimeout(); // Reset offline detection since we received valid content
        } else {
          addSystemLog('❌ JWT verification failed'); // Log verification failure
        }
      } else {
        addSystemLog('❌ No signed message in content update'); // Log missing JWT in message
      }
    });

    // Listen for display configuration changes
    channel.bind('display-config-updated', async (data) => {
      addSystemLog(`⚙️ Display config updated: ${JSON.stringify(data)}`);
      console.log('LiveDisplay: Display configuration updated:', data);
      
      // Force refresh current content by clearing and setting again
      setCurrentContent(null);
      
      // Show a brief message that config is updating
      setTimeout(() => {
        addSystemLog('🔄 Refreshing content after config change');
        console.log('LiveDisplay: Refreshing content after config change');
        // The scheduler should automatically send new content based on updated program
      }, 1000);
    });

    return () => {
      addSystemLog('Cleaning up WebSocket');
      if (offlineTimeoutRef.current) {
        clearTimeout(offlineTimeoutRef.current);
      }
      if (pusherRef.current) {
        pusherRef.current.disconnect();
        pusherRef.current = null;
      }
    }
  }, [displayInfo.program]);

  // Offline detection functions (no longer used, kept for compatibility)
  const startOfflineTimeout = () => {
    if (offlineTimeoutRef.current) {
      clearTimeout(offlineTimeoutRef.current)
    }
  }

  const resetOfflineTimeout = () => {
    if (offlineTimeoutRef.current) {
      clearTimeout(offlineTimeoutRef.current)
    }
    addSystemLog('✅ Reset offline timeout');
    setIsOffline(false)
  }

  // Handle content changes and scroll animation
  useEffect(() => {
    if (!currentContent || !iframeRef.current) return

    if (currentContent.type.toLowerCase() === 'link') {
      const iframe = iframeRef.current

      // If URL changed, reload the iframe content
      if (iframe.src !== currentContent.url) {
        addSystemLog(`🌐 Loading new URL: ${currentContent.url}`);
        console.log('LiveDisplay: Loading new URL:', currentContent.url)
        setIframeLoaded(false)
        if (iframe.load) {
          iframe.load(currentContent.url)
        } else {
          iframe.src = currentContent.url
        }
      }

      // Wait for iframe to load
      const handleIframeLoad = () => {
        try {
          const iframeDoc = iframe.contentDocument || iframe.contentWindow.document
          if (!iframeDoc) return

          addSystemLog(`🎬 Starting animation: ${currentContent.animation}`);
          console.log('LiveDisplay: Starting animation:', currentContent.animation)
          setIframeLoaded(true)
          startScrollAnimation(iframeDoc, currentContent.animation, currentContent.animation_speed || 1)
        } catch (error) {
          addSystemLog(`❌ Iframe access error: ${error.message}`);
          console.error('LiveDisplay: Error accessing iframe content:', error)
        }
      }

      iframe.addEventListener('load', handleIframeLoad)

      // If already loaded, start animation immediately
      if (iframe.contentDocument?.readyState === 'complete') {
        handleIframeLoad()
      }

      return () => {
        iframe.removeEventListener('load', handleIframeLoad)
        if (animationRef.current) {
          cancelAnimationFrame(animationRef.current)
        }
      }
    } else if (currentContent.type.toLowerCase() === 'embedding') {
      if (lastEmbedCodeRef.current !== currentContent.embed_code) {
        addSystemLog(`📺 Rendering embedding: ${currentContent.name}`);
        console.log('LiveDisplay: Rendering embedding:', currentContent.name)
        lastEmbedCodeRef.current = currentContent.embed_code
        setIframeLoaded(true)
      }
    }
  }, [currentContent])

  const startScrollAnimation = (doc, animation, speed) => {
    if (animationRef.current) {
      cancelAnimationFrame(animationRef.current)
    }

    const scrollHeight = doc.documentElement.scrollHeight
    const clientHeight = doc.documentElement.clientHeight
    const maxScroll = scrollHeight - clientHeight

    if (maxScroll <= 0) return // No scrolling needed

    let currentScroll = 0
    let direction = 1 // 1 for down, -1 for up
    let isWaiting = false

    const animate = () => {
      if (isWaiting) {
        animationRef.current = requestAnimationFrame(animate)
        return
      }

      switch (animation.toLowerCase()) {
        case 'none':
          // No animation, just display
          return

        case 'down':
          // Scroll down until bottom is reached, then stop
          currentScroll += speed
          if (currentScroll >= maxScroll) {
            currentScroll = maxScroll // Stop at bottom
            return // Stop animation
          }
          break

        case 'down&reset':
          // Scroll down, wait, then scroll up over 1 second, then repeat
          if (direction === 1) {
            currentScroll += speed
            if (currentScroll >= maxScroll) {
              isWaiting = true
              setTimeout(() => {
                direction = -1 // Start scrolling up
                isWaiting = false
              }, 2000) // Wait 2 seconds at bottom
            }
          } else {
            // Scroll up fast (1 second total)
            currentScroll -= maxScroll / 60 // Assuming 60fps, scroll up in 1 second
            if (currentScroll <= 0) {
              currentScroll = 0
              isWaiting = true
              setTimeout(() => {
                direction = 1 // Reset to scroll down again
                isWaiting = false
              }, 2000) // Wait 2 seconds at top
            }
          }
          break

        case 'down&up':
          // Scroll down, wait, then slowly scroll up
          if (direction === 1) {
            currentScroll += speed
            if (currentScroll >= maxScroll) {
              isWaiting = true
              setTimeout(() => {
                direction = -1
                isWaiting = false
              }, 2000) // Wait 2 seconds at bottom
            }
          } else {
            currentScroll -= speed
            if (currentScroll <= 0) {
              isWaiting = true
              setTimeout(() => {
                direction = 1
                isWaiting = false
              }, 2000) // Wait 2 seconds at top
            }
          }
          break

        case 'step':
          // Scroll down by viewport height and stop
          const stepSize = clientHeight
          if (currentScroll < maxScroll) {
            currentScroll = Math.min(currentScroll + stepSize, maxScroll)
          }
          if (currentScroll >= maxScroll) {
            return // Stop animation
          }
          break

        default:
          addSystemLog(`⚠️ Unknown animation type: ${animation}`);
          console.warn('LiveDisplay: Unknown animation type:', animation)
          return
      }

      doc.documentElement.scrollTop = currentScroll
      animationRef.current = requestAnimationFrame(animate)
    }

    animate()
  }

  // Always show standby when not loaded or no content
  const showStandby = !currentContent || !iframeLoaded

  // Memoize embed HTML to prevent iframe reloads on re-renders
  const embedHtml = useMemo(() =>
    currentContent?.embed_code ? { __html: currentContent.embed_code } : null,
    [currentContent?.embed_code]
  );

  // Render content based on type
  const renderContent = () => {
    if (!currentContent) return null;
    switch (currentContent.type.toLowerCase()) {
      case 'link':
        return (
          <iframe
            is="x-frame-bypass"
            ref={iframeRef}
            src={currentContent.url}
            className="content-iframe"
          />
        )

      case 'embedding':
        return (
          <div
            ref={iframeRef}
            dangerouslySetInnerHTML={embedHtml}
            className="content-iframe"
            style={{
              width: '100%',
              height: '100%',
              overflow: 'hidden'
            }}
          />
        )

      default:
        return (
          <div className="unsupported-content">
            <div>Unsupported Content Type: {currentContent.type}</div>
          </div>
        )
    }
  }


  // Main render: always show content container, overlay standby if needed
  return (
    <div className="full-screen">
      {renderContent()}

      {/* Offline overlay - renders ABOVE content when WebSocket is unreachable */}
      {isOffline && (
        <div
          style={{
            position: 'fixed',
            top: 0,
            left: 0,
            width: '100%',
            zIndex: 9999,
            backgroundColor: 'white',
            color: 'black',
            textAlign: 'center',
            padding: '10px 0',
          }}
        >
          <p style={{ fontSize: '14px', fontWeight: 'bold' }}>DISPLAY FAILED TO REACH YOUR SERVER</p>
          <p style={{ fontSize: '12px' }}>please check your connection</p>
        </div>
      )}

      {/* Standby overlay - only shows when no content loaded */}
      {showStandby && (
        <div className="standby-overlay">
          <div style={{ maxWidth: '1200px', margin: '0 auto' }}>
            <h1 className="h1">Presenter V4 - Live Display</h1>
            <p className="page-description" style={{ marginBottom: '15px' }}>
              I'm waiting for the next signal, it might take a moment...
            </p>
            <div style={{ marginBottom: '15px' }}>
              <DisplayInfo
                displayInfo={displayInfo}
                isRegistered={!!displayInfo.program}
                showRegistrationForm={false}
              />
            </div>

            {!displayInfo.program && (
              <div className="no-program-message">
                <strong className="text-accent">No Program Assigned</strong> - This display is not assigned to any program yet.
              </div>
            )}

            <div className="message-box">
              <h3 className="h3">System Activity Log</h3>
              {systemLogs.length === 0 ? (
                <p className="no-messages">
                  No activity yet. System is initializing...
                </p>
              ) : (
                <div ref={logContainerRef} className="log-container">
                  {systemLogs.map((log, index) => (
                    <div key={index} className="log-entry">
                      <span className="log-time">{log.time}</span>
                      <span className="log-message">{log.message}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default LiveDisplay