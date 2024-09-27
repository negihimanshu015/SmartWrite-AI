import { createContext, useState } from "react";
import run from "../api/api";

export const Context = createContext();

const ContextProvider = (props) => {

    const onsent = async (prompt) => {
        setResult("")
        setShowResult(true)
        const response = await run(Input)
        setResult(response)
        setInput("")        
    }

    const [Input,setInput] = useState("");
    const [Result,setResult] = useState("");
    const [ShowResult,setShowResult] = useState(false);
    
    const ContextValue = {
        onsent,
        Input,
        setInput,
        Result,
        setResult
    }

    return(
        <Context.Provider value={ContextValue}>
            {props.children}
        </Context.Provider>
    )
 }

 export default ContextProvider