import { useState, useEffect } from 'react'
import Pusher from 'pusher-js'
import './App.css'

function App() {
  const [connectionStatus, setConnectionStatus] = useState('Disconnected')
  const [messages, setMessages] = useState([])

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
      setConnectionStatus('Connected')
    })

    pusher.connection.bind('disconnected', () => {
      setConnectionStatus('Disconnected')
    })

    pusher.connection.bind('error', (error) => {
      setConnectionStatus('Error: ' + error.message)
    })

    // Subscribe to display channel for this client
    const channel = pusher.subscribe('display-updates')
    
    // Listen for test messages
    channel.bind('test-message', (data) => {
      setMessages(prev => [...prev, {
        id: Date.now(),
        message: data.message,
        timestamp: new Date().toLocaleTimeString()
      }])
    })

    // Cleanup on unmount (but not in React StrictMode double-mount)
    return () => {
      if (channel) {
        pusher.unsubscribe('display-updates')
      }
      // Don't disconnect in development to avoid StrictMode issues
      if (process.env.NODE_ENV === 'production') {
        pusher.disconnect()
      }
    }
  }, [])

  return (
    <div style={{ padding: '20px', fontFamily: 'Arial, sans-serif' }}>
      <h1>Presenter V3 - Display Client</h1>
      
      {/* Connection Status */}
      <div style={{ 
        padding: '10px', 
        marginBottom: '20px', 
        backgroundColor: connectionStatus === 'Connected' ? '#d4edda' : '#f8d7da',
        color: connectionStatus === 'Connected' ? '#155724' : '#721c24',
        border: `1px solid ${connectionStatus === 'Connected' ? '#c3e6cb' : '#f5c6cb'}`,
        borderRadius: '4px'
      }}>
        Status: {connectionStatus}
      </div>

      {/* Messages */}
      <div>
        <h2>Received Messages:</h2>
        {messages.length === 0 ? (
          <p>No messages received yet. Send a test message from the admin panel.</p>
        ) : (
          <ul>
            {messages.map(msg => (
              <li key={msg.id} style={{ marginBottom: '10px' }}>
                <strong>{msg.timestamp}:</strong> {msg.message}
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}

export default App
