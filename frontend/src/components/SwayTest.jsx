import React, { useState, useEffect } from 'react'

const SwayTest = () => {
  const [swayUrl, setSwayUrl] = useState('')

  useEffect(() => {
    // Simulate fetching from server/database
    const fetchSwayUrl = async () => {
      // Simulating API call - in real app this would be fetch('/api/sway-url') or similar
      const simulatedServerResponse = 'https://sway.cloud.microsoft/s/obcdiwDpiOhlRDB2/embed'
      setSwayUrl(simulatedServerResponse)
    }

    fetchSwayUrl()
  }, [])

  if (!swayUrl) {
    return <div style={{ 
      display: 'flex', 
      justifyContent: 'center', 
      alignItems: 'center', 
      height: '100vh',
      fontSize: '1.5rem'
    }}>Loading...</div>
  }

  return (
    <div style={{ 
      margin: 0, 
      padding: 0, 
      overflow: 'hidden',
      position: 'fixed',
      top: 0,
      left: 0,
      width: '100vw',
      height: '100vh'
    }}>
      <iframe 
        src={swayUrl}
        frameBorder="0" 
        marginHeight="0" 
        marginWidth="0" 
        sandbox="allow-forms allow-modals allow-orientation-lock allow-popups allow-same-origin allow-scripts" 
        scrolling="no" 
        style={{ 
          border: 'none', 
          width: '100vw', 
          height: '100vh' 
        }} 
        allowFullScreen 
        mozAllowFullScreen 
        msAllowFullScreen 
        webkitAllowFullScreen
        title="Sway Test"
      />
    </div>
  )
}

export default SwayTest