import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from './assets/vite.svg'
import './App.css'
import AssetList from './components/AssetList'

function App() {
  return (
    <div className="app-container">
      <header className="app-header">
        <h1>IT Management Portal</h1>
      </header>
      <main>
        <AssetList />
      </main>
    </div>
  )
}

export default App
