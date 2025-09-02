import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Index from './components/Index'
import RegisterAndStatus from './components/RegisterAndStatus'
import './App.css'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Index />} />
        <Route path="/register" element={<RegisterAndStatus />} />
      </Routes>
    </BrowserRouter>
  )
}

export default App
