// App.jsx
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Index from './components/Index'
import RegisterAndTest from './components/RegisterAndTest'
import LiveDisplay from './components/LiveDisplay'
import SwayTest from './components/SwayTest'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Index />} />
        <Route path="/register" element={<RegisterAndTest />} />
        <Route path="/live" element={<LiveDisplay />} />
        <Route path="/sway" element={<SwayTest />} />
      </Routes>
    </BrowserRouter>
  )
}

export default App