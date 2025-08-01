import { createRoot } from 'react-dom/client'
import App from './App.jsx'
import './bulma.css'
import ContextProvider from './context/context.jsx'

createRoot(document.getElementById('root')).render(
  <ContextProvider>
    <App />
  </ContextProvider>,
)
